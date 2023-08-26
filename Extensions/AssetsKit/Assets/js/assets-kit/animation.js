/*
   _   _  _ ___ __  __   _ _____ ___ ___  _  _
  /_\ | \| |_ _|  \/  | /_\_   _|_ _/ _ \| \| |
 / _ \| .` || || |\/| |/ _ \| |  | | (_) | .` |
/_/ \_\_|\_|___|_|  |_/_/ \_\_| |___\___/|_|\_|

This script contains some utility function to animate elements

```js

// To make an element disappear
await myElement.fadeOut()

// To make an element appear
await myElement.fadeIn()

// Same as animate but resolve the promise when the animation is finished
await myElement.animateAsync(myKeyframes, myOptions)

// A function to run an animation at will
let obj = myElement.animateLoop(myKeyframes, myOptions)
- obj.stop();
- obj.start();
- obj.iterationCount;
```
*/

declareNewBridge("animation", {
    FADE_ANIMATION: [
        { opacity: 0 },
        { opacity: 1 },
    ],

    POP_ANIMATION: [
        {scale: .1, opacity: 0},
        {scale: 1, opacity: 1}
    ],

    DEFAULT_ANIMATION_OPTIONS: {
        duration: 250,
        easing: "ease"
    },

    POP_OPTIONS : {
        duration: 750,
        easing: 'cubic-bezier(.79,-0.5,.23,1.46)'
    },

    animateAsync(element, keyframes, options=this.DEFAULT_ANIMATION_OPTIONS, disablePointer=true)
    {
        if (disablePointer)
            element.style.pointerEvents = "none";

        element.style.animation = ""
        let animation = element.animate(keyframes, options);

        return new Promise((res, _) => {
            animation.addEventListener("finish", ()=>{
                if (disablePointer)
                    element.style.pointerEvents = "";
                res()
            });
        });
    },

    fadeIn: async function (element, options=null)
    {
        element.style.display = "";
        await this.animateAsync(element, this.FADE_ANIMATION, {...this.DEFAULT_ANIMATION_OPTIONS, ...options});
    },


    fadeOut: async function (element, options=null)
    {
        await this.animateAsync(element, Array.from(this.FADE_ANIMATION).reverse(), {...this.DEFAULT_ANIMATION_OPTIONS, ...options});
        element.style.display = "none";
    },

    popIn: async function (element, options=null)
    {
        element.style.display = "";
        await this.animateAsync(element, this.POP_ANIMATION, {...this.POP_OPTIONS, ...options});
    },

    popOut: async function (element, options=null)
    {
        await this.animateAsync(element, Array.from(this.POP_ANIMATION).reverse(), {...this.POP_OPTIONS, ...options});
        element.style.display = "none";
    },


    /**
     * This function allow an element to run an animation indefinitely
     * This function return an object which has thoses properties :
     * | Property       | Type     | Description                                                |
     * |----------------|----------|------------------------------------------------------------|
     * | keyframes      | property | given animation keyframes                                  |
     * | options        | property | given animation options                                    |
     * | root           | property | Animated DOM Element                                       |
     * | running        | property | Is the animation currently running ?                       |
     * | started        | property | Is the element animated ?                                  |
     * | iterationCount | property | Number of animation iterations                             |
     * | start          | method   | Start to animate, don't have any effect if already started |
     * | stop           | method   | Stop the animate when the current animation stops          |
     */
    animateLoop: function(element, keyframes, options){
        let animation = {
            keyframes,
            options,

            root: element,
            running: false,
            started: false,

            iterationCount: 0,

            tick: async function(){
                if (!this.started)
                    return;

                this.running = true;
                await SharpAssetsKit.animation.animateAsync(this.root, this.keyframes, this.options);
                this.running = false;

                this.iterationCount++;
                if (this.started)
                    this.tick()
            },

            stop: function(){
                this.started = false;
            },

            start: function(){
                this.started = true;
                if (!this.running)
                    this.tick();
            }
        }

        animation.start();
        return animation;
    }
}, anim => {return {
    animateAsync: anim.animateAsync,
    fadeIn: anim.fadeIn,
    fadeOut: anim.fadeOut,
    popIn: anim.popIn,
    popOut: anim.popOut,
    animateLoop: anim.animateLoop,
}}, anim =>{
    Element.prototype.animateAsync = function(keyframes, options=this.DEFAULT_ANIMATION_OPTIONS, disablePointer=true){
        return anim.animateAsync(this, keyframes, options, disablePointer)
    }

    Element.prototype.fadeIn = function(options=null){ return anim.fadeIn(this, options); }
    Element.prototype.fadeOut = function(options=null){ return anim.fadeOut(this, options); }
    Element.prototype.popIn = function(options=null){ return anim.popIn(this, options); }
    Element.prototype.popOut = function(options=null){ return anim.popOut(this, options); }

    Element.prototype.animateLoop = function(keyframes, options){
        return anim.animateLoop(this, keyframes, options);
    }
})