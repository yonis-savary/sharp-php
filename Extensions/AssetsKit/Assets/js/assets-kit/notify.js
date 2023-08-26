
declareNewBridge("notify", {

    // Used to calculate a display time value based on word quantity
    NOTIFY_AGV_WORD_PER_SECOND: 10,


    NOTIFIER_ANIMATION: [
        {transform: `translate(-50%, -200%)`},
        {transform: `translate(-50%, 0%)`},
    ],
    NOTIFIER_ANIMATION_OPTION: {
        duration: 200,
        easing: "ease-in-out"
    },


    isRunning: false,
    queue: [],
    objects: {},

    currentObject: {},
    timeout: null,

    hideNotifyObject: async function()
    {
        let {animateAsync} = SharpAssetsKit.animation;

        if (this.timeout)
            clearTimeout(this.timeout);

        let element = this.currentObject.element;
        await animateAsync(element, Array.from(this.NOTIFIER_ANIMATION).reverse(), this.NOTIFIER_ANIMATION_OPTION);

        this.queue.shift()
        delete this.objects[this.currentObject.hash];
        element.remove();

        if (this.queue.length)
            this.displayNextNotifyObject()

        this.isRunning = false;
    },

    displayNextNotifyObject: async function()
    {
        let {animateAsync} = SharpAssetsKit.animation;
        let {svg} = SharpAssetsKit.svg;

        this.isRunning = true;

        let hash = this.queue[0];
        let current = this.currentObject = this.objects[hash];

        let element = current.element = document.createElement("section");
        element.classList = "notifier-log";
        element.innerHTML = `<section class="chip ${current.tone}">${svg(current.icon)}</section>` + `
        <section class="flex-column gap-1">
            <b>${current.title}</b>
            <p>${current.description}</p>
        </section>
        `
        document.body.appendChild(element);
        animateAsync(element, this.NOTIFIER_ANIMATION, this.NOTIFIER_ANIMATION_OPTION);

        this.timeout = setTimeout(this.hideNotifyObject.bind(this), 3000 + current.extraTime);
    },


    appendNotifyObject: function(object)
    {
        let {hash} = object;
        if (this.queue.includes(hash))
            return;

        this.queue.push(hash);
        this.objects[hash] = object;

        if (!this.isRunning)
            this.displayNextNotifyObject();
    },



    createNotifyObject: function(
        title,
        description="",
        icon="info",
        tone="blue"
    )
    {
        let {hashStr} = SharpAssetsKit.lang;

        return {
            hash: hashStr(title) + hashStr(description),
            title,
            description,
            icon,
            tone,
            extraTime: (description.match(/\b[^ ]+\b/g)?.length || 0) * (1000 / this.NOTIFY_AGV_WORD_PER_SECOND) ?? 500
        }
    },

    notify: function(title, description="", icon="info", tone="blue")
    {
        this.appendNotifyObject(
            this.createNotifyObject(title, description, icon, tone)
        );
    },

}, notify => {return {
    notify: notify.notify,
    notifyError: (title, description) => notify.notify(title, description, "exclamation", "red"),
    notifySuccess: (title, description) => notify.notify(title, description, "check", "green"),
    notifyWarning: (title, description) => notify.notify(title, description, "exclamation", "orange"),
    notifyInfo: (title, description) => notify.notify(title, description, "info", "blue"),
}})
