
declareNewBridge("lang", {

    /**
     * @var bool Is the current navigator on a mobile device ?
     */
    isMobile: function()
    {
        return (navigator.userAgent.match(/mobile|phone/i)?? []).length > 0
    },


    /**
     * @param {int} ms Miliseconds to wait
     * @returns A Promise is returned from the function, it is resolved when <ms> miliseconds passes
     */
    sleep: function(ms)
    {
        return new Promise(resolve => setTimeout(resolve, ms));
    },


    cloneObject: function(source)
    {
        return Object.assign({}, source);
    },


    clone: function(source)
    {
        return JSON.parse(JSON.stringify(source));
    },

    /**
     * @param {string} variable Variable name
     * @returns Value of the CSS variable
     */
    getCSSVariable: function(variable)
    {
        if (!variable.startsWith("--"))
            variable = "--"+variable;

        let style = getComputedStyle(document.body)
        return style.getPropertyValue(variable) ?? null;
    },


    /**
     * Allow you to map an object's entries with a callback
     * (key, value) are given to your callback
     *
     * @param {Object} object Object to map
     * @param {function} callback Mapping function
     * @returns Mapped entries
     */

    mapObjectEntries: function(object, callback)
    {
        let entries = Object.entries(object);
        return entries.map(entry => (callback)(...entry))
    },


    objectToFormData: function(object)
    {
        let form = new FormData();
        for (let [key, value] of Object.entries(object))
            form.append(key, value);
        return form;
    },


    hashStr: function(str)
    {
        return str.split('').reduce((prevHash, currVal) =>
            (((prevHash << 5) - prevHash) + currVal.charCodeAt(0))|0, 0);
    },





    siblingsOf: function(element)
    {
        // 'innerHTML' allow us to only select "true" nodes, and avoid text elements
        return Array.from(element.parentNode.childNodes)
        .filter(e => (e != element) && ('innerHTML' in e));
    },


    /*
    * Page Signature
    * _____________________________________
    * The page signature is a way to know if given content is legit
    * On every page load, a new Signature is generated
    *
    * So far, it is used for the html tag function,
    * if an htlk content contains the page signature, it is trusted and not escaped
    */

    newPageSignature: function ()
    {
        return hashStr(
            (new Date).toString() +
            window.location.toString() +
            Math.randomInt(0, 10**5)
        ) + ""
    },


    getPageSignature: function ()
    {
        this.pageSignature ??= this.newPageSignature()
        return this.pageSignature;
    },


    getSignatureComment: function ()
    {
        return "<!-- " + this.getPageSignature() + "  -->";
    },


    /**
     * Thanks https://stackoverflow.com/a/6234804 !
     * @param {String} toConvert Unescaped HTML
     * @returns Escaped HTML
     */
    escapeHTML: function (toConvert)
    {
        if (toConvert === null)
            return null;

        toConvert = toConvert + "";
        if (toConvert.indexOf(this.getPageSignature()) != -1)
            return toConvert.replace(this.getSignatureComment(), "");

        return toConvert
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    },

    /**
     * Tag function to write HTML in your document !
     */
    html: function(arr, ...parameters)
    {
        return this.getSignatureComment() + arr[0] + parameters.map((x,i) => this.escapeHTML(x) + arr[i+1]).join("");;
    }
}, lang => {return {
    isMobile: lang.isMobile,
    sleep: lang.sleep,
    cloneObject: lang.cloneObject,
    clone: lang.clone,
    getCSSVariable: lang.getCSSVariable,
    mapObjectEntries: lang.mapObjectEntries,
    objectToFormData: lang.objectToFormData,
    hashStr: lang.hashStr,
    newPageSignature: lang.newPageSignature,
    getPageSignature: lang.getPageSignature,
    getSignatureComment: lang.getSignatureComment,
    escapeHTML: lang.escapeHTML,
    html: lang.html,
}}, lang => {

    /**
     * This function allow you to avoid code like that
     * ```js
     * myArray.sort((a,b) => a.nest.value.way.too.long.to.access > b.nest.value.way.too.long.to.access ? 1:-1)
     * // ... and replace it with ...
     * myArray.sortByKey(x => x.nest.value.way.too.long.to.access)
     * ```
     * @param {function} callback Given an object/value, return the value to compare
     * @returns
     */
    Array.prototype.sortByKey = function(callback)
    {
        return this.sort((a,b)=> callback(a) >= callback(b) ? 1:-1 );
    };


    /**
     * Remove every duplicated elements from an array and return it
     */
    Array.prototype.uniques = function()
    {
        return Array.from(new Set(this));
    };


    /**
     * Almost same as `Array.reverse()`,
     * but return a copy of the array instead of modifying it
     */
    Array.prototype.invert = function()
    {
        return (Array.from(this)).reverse();
    }


    /**
     * Allow you to group objects with a key
     *
     * ```js
     * let arr = [
     *      {color: 'red', fruit:'apple'}
     *      {color: 'red', fruit:'strawberry'}
     *      {color: 'yellow', fruit:'banana'}
     *      {color: 'yellow', fruit:'pineapple'}
     * ]
     *
     * arr = arr.groupByKey(x => x.color)
     * //arr is now
     * {
     *     "red": [
     *         {color: 'red', fruit:'apple'}
     *         {color: 'red', fruit:'strawberry'}
     *     ],
     *     "yellow": [
     *         {color: 'yellow', fruit:'banana'}
     *         {color: 'yellow', fruit:'pineapple'}
     *     ]
     * }
     * ```
     *
     * @param {function} callback Function to get the group key
     * @returns Grouped values
     */
    Array.prototype.groupByKey = function(callback)
    {
        let groups = {};
        for (const element of this)
        {
            let key = (callback)(element);
            if (!(key in groups))
                groups[key] = [];
            groups[key].push(element)
        }
        return groups;
    }


    /**
     * Get the last element of an array, a default value can be given
     * (the default value is undefined by default)
     * @param {*} defaultValue Value to get if the array is empty
     * @returns
     */
    Array.prototype.last = function(defaultValue=undefined){
        return this[this.length-1] ?? defaultValue;
    }



    Array.prototype.chunk = function(size, start=0) {
        if (start >= this.length)
            return [];

        return [this.slice(start, start+size)].concat(this.chunk(size, start+size));
    }


    String.prototype.hash = function()
    {
        return lang.hashStr(this);
    };


    /**
     * Uppercase the first character of a string and lowercase the rest
     */
    String.prototype.toUpperCaseFirst = function()
    {
        return this.substring(0,1).toUpperCase() + this.substring(1).toLowerCase()
    };


    Document.prototype.waitForEvent =
    Element.prototype.waitForEvent = function(eventName){
        return new Promise((resolve, _)=>{
            this.addEventListener(eventName, resolve);
        });
    };


    Element.prototype.siblings = function(){ return SharpAssetsKit.lang.siblingsOf(this)};

    Element.prototype.getAttributes = function(...names){
        return names.map(n => this.getAttribute(n));
    }


    Element.prototype.appendChilds = function(...childs){
        childs = Array.from(childs);
        for (const c of childs)
            this.appendChild(c)
    }



    document.nodeFromHTML = (html, type="section") => {
        let parent = document.createElement(type);
        parent.style.display = "none";
        parent.innerHTML = html;
        document.body.appendChild(parent);

        let child = parent.querySelector("*");

        let element = document.createElement(type);

        let nodes = child.childNodes;
        for (let i=0; i<nodes.length; i=0)
            element.appendChild(nodes[i]);

        for (const attr of child.attributes)
            element.setAttribute(attr.name, attr.value);

        if (child.id)
            element.id = child.id;

        parent.remove();
        return element;
    }




    /**
     * IMPORTANT: DO NOT USE THIS FOR ANY SECURITY SYSTEM
     * @returns A random digit between 0 and 1
     */
    Math.randomBit = _ => Math.floor(Math.random()*2);

    /**
     * IMPORTANT: DO NOT USE THIS FOR ANY SECURITY SYSTEM
     * @returns A random float between min and max
     */
    Math.randomFloat = (min, max) => (Math.random() * (max-min)) + min;

    /**
     * IMPORTANT: DO NOT USE THIS FOR ANY SECURITY SYSTEM
     * @returns A random int between min and max (max included)
     */
    Math.randomInt = (min, max) => Math.floor(Math.randomFloat(min, max+1));



    Math.range = (max, start=0, inclusive=false) => {
        let size = (max-start) + inclusive*1
        return (new Array(size))
        .fill(null)
        .map((_,i) => i+start);
    }


    Math.average = (...values) => (values.reduce((acc,cur)=>acc+cur, 0))/values.length;


    Math.map = (x, fromScaleStart, fromScaleEnd, toScaleStart, toScaleEnd) => {
        let [a, b, c, d] = [fromScaleStart, fromScaleEnd, toScaleStart, toScaleEnd];

        let deltaFrom = b-a;
        let deltaX = x-a;

        let xScale = deltaX/deltaFrom;

        let deltaTo = d-c;

        return (deltaTo * xScale) + c;
    };
});