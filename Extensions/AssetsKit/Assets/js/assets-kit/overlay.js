
/*
  _____   _____ ___ _      ___   _____
 / _ \ \ / / __| _ \ |    /_\ \ / / __|
| (_) \ V /| _||   / |__ / _ \ V /\__ \
 \___/ \_/ |___|_|_\____/_/ \_\_| |___/

Overlay Example:

```html
    <!-- only need "overlay" class and an id to work -->
    <div class="overlay" id="myOverlay">
        <div class="content">
            <h1>Some content here !</h1>
        </div>
    </div>

    <!-- You can also have direct overlays openers -->
    <button overlay="myOverlay">Click me</button>
```

```js
    openOverlay(myOverlay)   // Works with direct DOM element
    openOverlay("myOverlay") // Also works with element's id
    closeOverlay()

```
*/


declareNewBridge("overlay", {

    stack: [],

    OVERLAY_CONTENT_SLIDE: [
        {transform: 'translateY(100%)'},
        {transform: 'translateY(0%)'},
    ],

    OVERLAY_DESKTOP_SLIDE : [
        { transform: 'translateY(30%) scale(0, .5)', opacity: 0, borderRadius: `50% 50% 100% 100%` },
        { transform: 'translateY(0%) scale(1, 1)', opacity: 1, borderRadius: '1em 1em 1em 1em'},
    ],

    /**
     * Open an overlay by setting its display style attribute to <display (flex by default)>
     * @param {DOMElement|String} element DOMElement or element's Id
     * @param {string} display By default, the display value is set to flex, but you can change it
     */
    open: async function (element)
    {
        let {animateAsync, fadeIn, FADE_ANIMATION} = SharpAssetsKit.animation;
        let {isMobile} = SharpAssetsKit.lang;

        // We consider a string as an Id
        if (typeof element == "string")
            element = document.getElementById(element);

        if ((!element) || (!("nodeName" in element)))
            return console.warn("Cannot open overlay, invalid element", element)

        this.stack.push(element);

        let content = element.querySelector(".content");
        let animation = isMobile() ? this.OVERLAY_CONTENT_SLIDE : this.OVERLAY_DESKTOP_SLIDE;

        content.style.display = "none";

        element.style.display = "flex";
        await animateAsync(element, FADE_ANIMATION, {duration: 200, easing: 'ease'});

        content.style.display = "";
        await animateAsync(content, animation, {duration: 200, easing: "ease-in-out"});

        element.dispatchEvent(new Event('opened'));
    },



    /**
     * Simply close the last opened overlay by setting its display style value to none
     */
    close: async function ()
    {
        let {animateAsync, fadeOut, FADE_ANIMATION} = SharpAssetsKit.animation;
        let {isMobile} = SharpAssetsKit.lang;

        if (!this.stack.length)
            return;

        let toClose = this.stack.pop();

        let content = toClose.querySelector(".content");

        let animation = isMobile() ? this.OVERLAY_CONTENT_SLIDE : this.OVERLAY_DESKTOP_SLIDE;

        await animateAsync(content, Array.from(animation).reverse(), {duration: 200, easing: "ease"});
        content.style.display = "none";

        await animateAsync(toClose, Array.from(FADE_ANIMATION).reverse(), {duration: 200, easing: 'ease'});
        toClose.style.display = "none";

        toClose.dispatchEvent(new Event('closed'));
    },



    /**
     * Close every opened Overlays
     */
    closeAll: async function() {
        let size = this.stack.length;
        for (let i=0; i<size; i++)
            await closeOverlay();
    },



    /**
     * Use this function if a script added some overlay-related elements to the body
     */
    addListeners: function()
    {
        document.querySelectorAll("[overlay]").forEach( el => {
            el.addEventListener("click", ()=>this.open(el.getAttribute("overlay")));
        });
        document.querySelectorAll(".overlay").forEach( el => {
            el.style.display = "none";
        })
        document.querySelectorAll(".overlay:not([locked])").forEach( el => {
            el.addEventListener("click", (event)=>{
                if (event.target.classList.contains("overlay"))
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    event.stopPropagation();

                if (event.target == el)
                    this.close();
            })
        })
    }
}, overlay => {return {
    openOverlay: overlay.open,
    closeOverlay: overlay.close,
    closeAllOverlays: overlay.closeAll,
    addOverlayListeners: overlay.addListeners
}})



document.addEventListener("DOMContentLoaded", SharpAssetsKit.overlay.addListeners)