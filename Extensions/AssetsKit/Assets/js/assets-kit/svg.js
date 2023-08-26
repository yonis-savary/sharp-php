/*
 _____   _____
/ __\ \ / / __|
\__ \\ V / (_ |
|___/ \_/ \___|

SVG System explaination

An svg can be served from the server, for more performances,
we put them in cache

If we want to load an unknown svg, we simply put a placeholder
and add its name to loadQueue, which is a SET that contains name of icons
to load

Also, every SVG is saved with their original size, which is changed
when loading them through the cache

*/

declareNewBridge("svg", {

    SVG_ROUTE: "/assets/svg",

    loadQueue: new Set(),
    timeout: null,

    key: function (name) {
        return `${window.location.host}.svg.store.${name}`;
    },


    fetch: async function (name){
        let body = await fetch(`${this.SVG_ROUTE}?name=${name}`);

        content = body.ok ? await body.text(): '';
        localStorage.setItem(this.key(name), content);
    },



    refresh: async function (){
        if (this.loadQueue.length == 0)
            return;

        await Promise.all(Array.from(this.loadQueue).map(this.fetch.bind(this)));
        this.loadQueue.clear();

        let elements = document.querySelectorAll(".svg-placeholder");
        for (let el of elements)
        {
            if (!('parentNode' in el))
                continue;

            let icon = el.getAttribute("icon");
            let size = parseInt(el.getAttribute("size") ?? 24)
            el.outerHTML = this.svg(icon, size);
        }
        this.timeout = null;
    },


    svg: function (icon, size=24){
        let content = localStorage.getItem(this.key(icon));
        let signature = typeof getPageSignature === "function" ? getPageSignature(): "dummysig";

        if (content)
        {
            return content.replace(/(height|width)=\"\d+\"/g, `$1=\"${size}"`).replace(/>/, `sig="${signature}">`);
        }
        else
        {
            this.loadQueue.add(icon);
            this.timeout ??= setTimeout(this.refresh.bind(this), 250);

            return `<svg class="svg-placeholder" icon="${icon}" size="${size}" sig="${signature}"></svg>`;
        }
    }
}, svg => {return {
    svgKey : svg.key,
    fetchSVG : svg.fetch,
    refreshSVG : svg.refresh,
    svg : svg.svg,
}});