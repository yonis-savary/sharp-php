/*
   _   ___ ___ ___  ___     __  __ ___ _  _ _   _
  /_\ / __|_ _|   \| __|   |  \/  | __| \| | | | |
 / _ \\__ \| || |) | _|    | |\/| | _|| .` | |_| |
/_/ \_\___/___|___/|___|   |_|  |_|___|_|\_|\___/

Aside style menu

```html
<aside>
    <span for="menu1">Menu1</span>
    <span for="menu2">Menu2</span>
    <span for="menu3">Menu3</span>
</aside>

<!-- Every target menu must be in the same node -->
<section>
    <section id="menu1"> <h1>Menu 1</h1> </section>
    <section id="menu1"> <h1>Menu 2</h1> </section>
    <section id="menu1"> <h1>Menu 3</h1> </section>
</section>
```

```js
```
Nothing ! This script analyse your HTML Layout as soon as the page is ready
Still, you can launch again the scan function with `scanForAside()`

Also, you can have multiple aside menu per page !
(That is why targets must be grouped inside containers)

*/

declareNewBridge("aside", {

    openAside: async function (link)
    {
        let {lang, animation} = SharpAssetsKit;

        link.parentNode.querySelector(".active")?.classList.remove("active");
        link.classList.add("active");

        let target = document.getElementById(link.getAttribute("for"));

        if (!target)
            return console.warn("[aside] menu not found : " + link.getAttribute("for"));

        let siblings = lang.siblingsOf(target);

        await Promise.all(siblings.map( e => animation.fadeOut(e, {duration: 150})));

        await animation.fadeIn(target, {duration: 150});
    },


    addAsideListeners: function ()
    {
        for (const menu of document.querySelectorAll("aside, .aside"))
        {
            let links = menu.querySelectorAll("[for]");
            links.forEach( x =>{
                x.addEventListener("click", _ => this.openAside(x));
            });

            if (links.length)
                links[0].click();
        }
    }
});


document.addEventListener("DOMContentLoaded", SharpAssetsKit.aside.addAsideListeners);