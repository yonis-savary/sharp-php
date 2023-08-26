
declareNewBridge("API", {

    API_URL: "/api",

    API_TOKEN_ENABLED : true,
    API_TOKEN_VALUE   : "123456789",

    CREATE_METHOD : "POST",
    READ_METHOD   : "GET",
    UPDATE_METHOD : "PATCH",
    DELETE_METHOD : "DELETE",


    lastResponse : null,
    lastResponseText : null,

    /**
     * Build a proper URL from an array of strings
     * @param {string[]} parts String Parts of your URL
     * @returns Cleaned URL made of given parts
     * @note The returned url starts with a '/' and ends with nothing
     * @example apiURL("/hey", "api", "hello/goodbye") // "/hey/api/hello/goodbye"
     */
    url: function(...parts) {
        let res = "/" + parts.map(e => e.toString().replace(/^\/|\/$/g, "")).join("/");

        if (!(res + "/").startsWith(this.API_URL+"/"))
            res = this.url(this.API_URL, res);

        return res;
    },


    /**
     * Fetch your API for some Data
     * Build a GET url for GET method, POST Body for other methods
     * Try to return the JSON content, returns body's text is JSON Parsing failed
     *
     * @param {string} url Url To Fetch (prepends API_URL if absent)
     * @param {object} data Data object for Request body
     * @param {string} method HTTP Method
     * @param {function} optionsMapper You can edit the request options with a provided function
     * @returns API JSON Response or Text if parsing failed
     */
    fetch: async function (url, data={}, method="GET", optionsMapper=null)
    {
        url = this.url(url);

        let headers = {};

        if (this.API_TOKEN_ENABLED)
            headers["Authorization"] = `Basic ${this.API_TOKEN_VALUE}`;

        method = method.toUpperCase();
        let options = { method, headers };
        let form = objectToFormData(data);

        if (method === "POST")
            options.body = form;
        else
            url += (url.indexOf("?") == -1 ? '?': '&') + (new URLSearchParams(form)).toString();

        if (typeof optionsMapper == "function" )
            options = optionsMapper(options);

        let res = this.lastResponse = await fetch(url, options);
        let text = this.lastResponseText = await res.text();

        let statusType = Math.floor(this.lastResponse.status/100);

        if (statusType == 5 || statusType == 4) // Throw error on 5xx and 4xx responses
        {
            let err = new Error(res.statusText);
            err.responseBody = text;
            throw err;
        }

        if (this.lastResponse.status === 204)
            return null;

        try
        {
            return await JSON.parse(text);
        }
        catch (e)
        {
            console.error("Cannot parse JSON", text);
            throw e
        }
    },

    fetchJSON : function(url, data, method="POST") {
        return this.fetch(
            url,
            {},
            method,
            options => {
                options.body = JSON.stringify(data)
                options.headers["Content-Type"] = "application/json";
                return options;
            }
        )
    },

    create: async function (model, data){
        return (await this.fetch(model, data, this.CREATE_METHOD)).insertedId;
    },

    read: function (model, data){
        return this.fetch(model, data, this.READ_METHOD);
    },

    update: function (model, data){
        return this.fetch(model, data, this.UPDATE_METHOD);
    },

    delete: function (model, data){
        return this.fetch(model, data, this.DELETE_METHOD);
    },

    createMultiple: async function (model, data) {
        return await this.fetchJSON(this.url(model, "/multiple"), data, this.CREATE_METHOD);
    }
}, api => {return {
    apiLastResponse: _ => api.lastResponse,
    apiLastResponseText: _ => api.lastResponseText,
    apiUrl: api.url,
    apiFetch: api.fetch,
    apiFetchJSON: api.fetchJSON,
    apiCreate: api.create,
    apiRead: api.read,
    apiUpdate: api.update,
    apiDelete: api.delete,
    apiCreateMultiple: api.createMultiple,
}})