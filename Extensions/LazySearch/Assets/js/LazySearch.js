if (!('addMenuListeners' in window))
    throw new Error("[assets-kit/menu.js] is needed")

if (!('svg' in window))
    throw new Error("[assets-kit/svg.js] is needed")


const LOCALES = {
    "en": {
        dict: {
            selectAllLabel     : "All",
            filtersTitle       : "Filters",
            resetFilters       : "Reset filters",
            exportLabel        : "Export",
            searchPlaceholder  : "Search..."
        },
        functions: {
            dateTransform: e => e
        }
    },
    "fr": {
        dict: {
            selectAllLabel     : "Tous",
            filtersTitle       : "Filtres",
            resetFilters       : "Réinitialiser",
            exportLabel        : "Exporter",
            searchPlaceholder  : "Rechercher..."
        },
        functions: {
            dateTransform: e => e.split("-").reverse().join("/")
        }
    }
}

const LOC = LOCALES[(typeof LAZYSEARCH_CONFIGURATION !== "undefined" ? LAZYSEARCH_CONFIGURATION.locale : null ) ?? 'en'];

/**
 * Events
 *
 * LazySearchInitialized
 * LazySearchRefreshed
 *
 * LazySearchLoaded
 *
 */

class LazySearch
{
    url = null;
    root = null;
    dom = {};

    meta = null;
    data = null;

    renderCallbacks = [];

    response = null;
    params = {
        flags: {
            initialized: false,
            extractInfos: true,
            builtBody: false,
            builtBodyEventDispatched: false,
            firstBuild: true,
            allowFilters: true
        },
        mode: "data",
        page: 0,
        size: 50,
        search: "",
        filters: {},
        sorts: [],
        extras: {}
    }
    flags = this.params.flags;

    renderFunctions = []


    constructor(root)
    {
        this.id = root.id || `lst_${((new Date).getTime()).toString(16)}`;
        this.root = root;
        this.url = root.getAttribute("url")

        this.flags.allowFilters = !this.root.hasAttribute("no-filters");
        this.flags.allowTranslate = !this.root.hasAttribute("no-translate");

        this.root.innerHTML = `
        <section class="lazySearchGrid" style="display: grid; gap: 1em; grid-template-area:
            'none', 'pagination'
            'filters', 'content';
            grid-template-columns: minmax(auto, 0px) auto;
        ">
            ${(!this.flags.allowFilters) ? "": `
            <section style="grid-area: 2/1/3/2" class="flex-column aside-menu">
                <section class="card">
                    <section class="flex-column">
                        <b class="svg-text">${svg("funnel")}${LOC.dict.filtersTitle}</b>
                        <section class="flex-column hide-empty gap-2 filters"></section>
                    </section>
                    <section class="hide-empty extraAside"></section>
                </section>
            </section>
            `}
            <section
                style="grid-area: 1/2/2/3; width: 100%"
                class="flex-row justify-between"
            >
                <section class="flex-row align-center">
                    <input type="search" placeholder="${LOC.dict.searchPlaceholder}" name="${this.url}" class="search">
                    <button class="button blue secondary svg-text resetButton">${svg("arrow-repeat")} ${LOC.dict.resetFilters}</button>
                    <button class="button violet secondary svg-text exportButton">${svg("file-earmark-arrow-down")} ${LOC.dict.exportLabel}</button>
                </section>
                <section class="flex-row gap-1 align-end pagination"></section>
            </section>
            <section style="grid-area: 2/2/3/3" class="content-wrapper">
                <table class="content table"></table>
            </section>
        </section>
        `

        const putDOM = classname => this.dom[classname] = this.root.querySelector(`.${classname}`);
        ["filters", "pagination", "search", "content", "resetButton", "exportButton", "extraAside"].forEach(putDOM);

        if (this.dom.search)
            this.dom.search.addEventListener("change", ()=>{
                this.params.search = this.dom.search.value || null;
                this.params.page = 0;
                this.refresh()
            })

        this.dom.resetButton?.addEventListener("click", ()=>this.reset())
        this.dom.exportButton?.addEventListener("click", ()=>this.export())

        this.observer = new ResizeObserver(this.refreshTranslate.bind(this));
        this.observer.observe(this.root);

        ;(async _ => {
            if (this.url)
                await this.refresh()

            this.dispatchEvent("LazySearchInitialized");
        })();
    }

    addRenderFunction(...callbacks)
    {
        this.renderCallbacks.push(...callbacks)
        this.refresh()
    }

    refreshTranslate()
    {
        if (!this.flags.allowFilters)
            return;

        if (!this.flags.allowTranslate)
            return;

        if (isMobile())
            return;

        this.root.style.transform = "";
        let box = this.root.getBoundingClientRect();
        this.root.style.transform = `translateX(-${box.left/2}px)`;
    }

    async export()
    {
        let paramsMock = Object.assign({}, this.params);
        paramsMock.mode = "file";

        // TODO : Find a way to download with a POST query
        window.open(`${this.url}?params=${JSON.stringify(paramsMock)}`)
    }

    async reset()
    {
        this.params.page = 0;
        this.params.search = "";
        this.params.filters =  {};
        this.params.sorts =  [];
        this.flags.extractInfos = true;
        this.refresh();
    }

    async dispatchEvent(eventName)
    {
        this.root.dispatchEvent(new CustomEvent(eventName, {detail: {
            instance: this
        }}));
    }

    async refresh(forceReload=false)
    {
        if (forceReload)
            this.flags.extractInfos = true;

        await this.dom.content.animateAsync([
            {opacity: 1},
            {opacity: .5},
        ], {duration: 50});
        this.dom.content.style.opacity = .5;

        let body = await fetch(this.url , {
            body: JSON.stringify(this.params),
            method: "POST",
            headers: {'Content-Type': 'application/json'}
        });
        body = this.response = await body.json();

        await this.dom.content.animateAsync([
            {opacity: .5},
            {opacity: 1},
        ], {duration: 50});
        this.dom.content.style.opacity = 1;

        let {meta, data, options, resultsCount} = body;

        let gotDefault = this.flags.initialized ? null: this.applyDefaults(options);

        if (this.flags.extractInfos)
        {
            this.flags.extractInfos = false;
            this.buildFilters(meta);
            this.refreshTranslate();
        }

        this.buildPagination(resultsCount)
        this.buildTable(data, meta, options);


        if (this.flags.initialized)
            return this.dispatchEvent("LazySearchRefreshed");


        this.flags.initialized = true;
        if (gotDefault)
            this.refresh();
    }


    async applyDefaults(options)
    {
        let {defaultSorts, defaultFilters} = options;

        if ((!defaultSorts) && (!defaultFilters))
            return false;

        if (typeof defaultSorts === "object" && Object.keys(defaultSorts).length)
            this.params.sorts = defaultSorts;

        if (typeof defaultFilters === "object" && Object.keys(defaultFilters).length)
            this.params.filters = defaultFilters;

        let filters = this.params.filters;
        Object.keys(this.params.filters).forEach(field => {
            if (!Array.isArray(filters[field]))
                filters[field] = [filters[field]];
        })
    }

    async buildTable(data, meta, options)
    {
        let isIgnored = field => options.ignores.includes(field)
        let displayable = meta.fields.map(x => x.alias).filter(x => !isIgnored(x));

        let links = {};
        options.links.forEach(link => {
            links[link.field] = row => `${link.prefix}${row[link.value]}`
        });
        let isLink = field => Object.keys(links).includes(field);

        const safeAttrVal = value => (value ?? "").toString().replaceAll("\"", "\\\"").replaceAll(">", "&gt;").replaceAll("<", "&lt;");

        this.dom.content.innerHTML = `
        <thead>
            ${displayable.map(field => `
            <th>
                <section
                    class="flex-row sort-button align-center ${this.params.sorts[0] == field ? "fg-blue": ""}"
                >
                    ${this.params.sorts[0] !== field ?
                        `<span class="svg-text sort-asc" field="${field}">${svg('filter', 18)}</span>`:
                        this.params.sorts[1] === "ASC" ?
                            `<span class="svg-text sort-desc" field="${field}">${svg('sort-alpha-down', 18)}</span>`:
                            `<span class="svg-text sort-none" field="${field}">${svg('sort-alpha-down-alt', 18)}</span>`

                    }
                    <b>${field}<b>
                </section>
            </th>
            `).join("")}
        </thead>
        <tbody>
            ${data.map(row => `
                <tr ${meta.fields.map(field => `${field.alias}="${safeAttrVal(row[field.alias])}"`).join(" ")}>
                ${meta.fields.map(field =>
                    isIgnored(field.alias) ?
                        ``:
                        isLink(field.alias) ?
                            `<td ${field.alias}="${safeAttrVal(row[field.alias])}" title="${safeAttrVal(row[field.alias])}"><a href="${links[field.alias](row)}">${this.formatData(row[field.alias])}</a></td>`:
                            `<td ${field.alias}="${safeAttrVal(row[field.alias])}" title="${safeAttrVal(row[field.alias])}">${this.formatData(row[field.alias])}</td>
                `).join("")}
                ${(this.renderCallbacks ?? []).map(callback => callback(row, meta.fields)).join("")}
                </tr>
                `
            ).join("")}
        </tbody>
        `

        const getSortCallback = (input, mode) => {
            return event => {
                if (!mode)
                    this.params.sorts = [];
                else
                    this.params.sorts = [input.getAttribute("field"), mode];

                this.params.page = 0;
                this.refresh();
            }
        }

        this.dom.content.querySelectorAll(".sort-asc").forEach(button => {
            button.parentNode.addEventListener("click", getSortCallback(button, "ASC"))
        })
        this.dom.content.querySelectorAll(".sort-desc").forEach(button => {
            button.parentNode.addEventListener("click", getSortCallback(button, "DESC"))
        })
        this.dom.content.querySelectorAll(".sort-none").forEach(button => {
            button.parentNode.addEventListener("click", getSortCallback(button, null))
        })

    }

    async buildPagination(resultsCount)
    {
        let maxPage = Math.ceil(resultsCount / this.params.size);
        let range = Array(5).fill(0).map((_,i) => this.params.page + i - 1 ).filter(x => 0 < x && x <= maxPage);
        let minRange = Math.min(...range);
        let maxRange = Math.max(...range);

        if (maxPage == 0)
            return this.dom.pagination.innerHTML = "";

        const pageButton = page => `<button page="${page}" class="button icon ${page === this.params.page+1 ? "active" :''} ">${page}</button>`

        this.dom.pagination.innerHTML = `
            ${minRange <= 1 ? "":`${pageButton(1)}...`}
            ${range.map(pageButton).join("")}
            ${maxRange >= maxPage ? "": `...${pageButton(maxPage)}`}
        `

        this.dom.pagination.querySelectorAll("button[page]").forEach(button => {
            button.addEventListener("click", _ =>{
                this.params.page = parseInt(button.getAttribute("page"))-1;
                this.refresh();
            })
        })
    }

    async buildFilters(meta)
    {
        if (!this.flags.allowFilters)
            return;

        this.dom.filters.innerHTML = `
            ${meta.fields.filter(x => (x.possibilities ?? []).length).map(field => `
                <details class="flex-column gap-0" ${(this.params.filters[field.alias] ?? []).length ? "open": ""}>
                    <summary>
                        <b>${field.alias}</b>
                    </summary>
                    <label class="flex-row align-center gap-1">
                        <input type="checkbox" checked field="${field.alias}" class="filter-all-checkbox">
                        ${LOC.dict.selectAllLabel}
                    </label>
                    <section class="padding-left-2 flex-column gap-0 scrollable max-vh-20">
                        ${field.possibilities.sort().map((x,i) => `
                        <label class="flex-row gap-1 filter-label">
                            <input
                                type="checkbox"
                                field="${field.alias}"
                                index="${i}"
                                class="filter-checkbox"
                                ${(this.params.filters[field.alias]??[]).includes(x) ? '': 'checked'}
                                value="${x}"
                            >
                            ${this.formatData(x)}
                        </label>
                        `).join("")}
                    </section>
                </details>
            `).join("")}
        `
        this.dom.filters.querySelectorAll(".filter-checkbox").forEach(checkbox => {
            let field = checkbox.getAttribute("field");
            let index = parseInt(checkbox.getAttribute("index"));

            let value = meta.fields.find(x => x.alias == field).possibilities[index];

            checkbox.addEventListener("change", event => this.updateFilter(event, value));
        });

        this.dom.filters.querySelectorAll(".filter-all-checkbox").forEach(checkbox => {
            checkbox.addEventListener("change", ()=>{
                let field = checkbox.getAttribute("field");
                this.dom.filters.querySelectorAll(`.filter-checkbox[field='${field}']`).forEach(target => {
                    target.checked = checkbox.checked;
                });

                if (checkbox.checked)
                    this.params.filters[field] = [];
                else
                    this.params.filters[field] = meta.fields.find(x => x.alias == field).possibilities;
                this.refresh()
            });
        })
    }

    updateFilter(event, value)
    {
        let field = event.target.getAttribute("field");

        this.params.filters[field] ??= [];
        if (event.target.checked)
            this.params.filters[field] = this.params.filters[field].filter(x => x != value);
        else
            this.params.filters[field].push(value);

        this.params.page = 0;
        this.refresh();
    }

    formatData(data)
    {
        if (data === null || typeof data == "undefined")
            return "N/A";

        if (data.toString().match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/))
            return LOC.functions.dateTransform(data.toString());

        if (data.toString().match(/^(http|www)/))
            return html`<a href="${data}">${data}</a>`

        return html`${data}`;
    }


    setExtra(extras) { this.params.extras = extras ; this.refresh(); }
    getExtra() { return this.params.extras }
    editExtra(callback) { this.setExtra(callback(this.getExtra())); }
}

window.lazySearchInstances = {}

async function refreshLazySearch()
{
    let promises = []
    document.querySelectorAll(".lazySearch").forEach(table => {
        promises.push( new Promise(res => table.addEventListener("LazySearchInitialized", res) ) );
        let instance = new LazySearch(table);
        window.lazySearchInstances[instance.id] = instance;
    });

    await Promise.allSettled(promises)
    document.dispatchEvent(new Event("LazySearchLoaded"));
}

document.addEventListener("DOMContentLoaded", refreshLazySearch);