/*
 __  __ ___ _  _ _   _ ___
|  \/  | __| \| | | | / __|
| |\/| | _|| .` | |_| \__ \
|_|  |_|___|_|\_|\___/|___/

The menu feature is very similar the the overlay one,
the only difference is that, a menu is not displayed at the center of the screen,
it is directly placed around an element (button, link...etc)

How to use it :

```html
<button menu="myMiniForm">Rename File</button>

<section class="menu" id="myMiniForm">
    <b>Rename File</b>
    <input type="text">
    <button class="button">Save</button>
</section>
```

You can also open it directly
```js
// Open myMenu next to myButton on click
myButton.onclick = ()=> openMenu(myMenuSection, myButton)
```

If you want to have only one menu openable in a section of your page,
you can group them with a `name` attribute

*/





class AssetsKitMenu
{
    static menuBackgroundForMobile = null;



    static MENU_SLIDE_IN = SharpAssetsKit.lang.isMobile() ? [
        { transform: 'translateY(100%)' },
        { transform: 'translateY(0%)' },
    ] : [
        { transform: 'translateY(3em)', opacity: 0 },
        { transform: 'translateY(0em)', opacity: 1 },
    ]


    static MENU_SLIDE_OUT = SharpAssetsKit.lang.isMobile() ? [
        { transform: 'translateY(0%)' },
        { transform: 'translateY(100%)' },
    ] : [
        { transform: 'translateY(0em)', opacity: 1 },
        { transform: 'translateY(-3em)', opacity: 0 },
    ]



    menu = null;
    button = null;
    direction = null;
    isMobile = false;
    opened = true;

    resizeAndPosition()
    {
        let {menu, button, direction} = this;

        let mBox = menu.getBoundingClientRect();

        let buttonIsHTML = 'getBoundingClientRect' in button;

        let bBox = buttonIsHTML ? button.getBoundingClientRect() : {
            left: button.x,
            top: button.y,
            width: 1,
            height: 1
        };

        const spacing = buttonIsHTML ? 6: 12;

        if (!buttonIsHTML)
            button = null;

        // xn, yn, wn, hn represents X and Y coords, width and height
        let [buttonX, buttonY, buttonWidth, buttonHeight] = [bBox.left, bBox.top, bBox.width, bBox.height];
        let [ _, __, menuWidth, menuHeight] = [mBox.left, mBox.top, mBox.width, mBox.height];

        let isDirectionEnabled = (dir) => direction === dir || (button && button.hasAttribute(dir));

        if (isDirectionEnabled("top") || isDirectionEnabled("bottom"))
        {
            menuWidth = Math.max(menuWidth, buttonWidth);
            menu.style.width = menuWidth < 2 ? "": menuWidth+"px";
        }

        // Initial position
        let vector = []; // Given coordinate to vector represent the center of the menu
        if (isDirectionEnabled("top"))
        {
            menu.classList.add("menu-top")
            vector = {
                x: buttonX + buttonWidth/2,
                y: buttonY - (menuHeight/2 + spacing)
            };
        }
        else if (isDirectionEnabled("left"))
        {
            menu.classList.add("menu-left")
            vector = {
                x: buttonX - (menuWidth/2 + spacing),
                y: buttonY + buttonHeight/2
            };
        }
        else if (isDirectionEnabled("bottom"))
        {
            menu.classList.add("menu-bottom")
            vector = {
                x: buttonX + buttonWidth/2,
                y: buttonY + (menuHeight/2 + buttonHeight + spacing)
            };
        }
        else  // Right by default
        {
            menu.classList.add("menu-right")
            vector = {
                x: buttonX + (buttonWidth + menuWidth/2 + spacing),
                y: buttonY + buttonHeight/2
            };
        }


        // Fixed position
        // Fix the menu position if part of it is off-screen

        let [ x, y, w, h ] = [ vector.x, vector.y, menuWidth, menuHeight ];
        let topLeft     = {x: x - w/2, y: y - h/2 }
        let bottomRight = {x: x + w/2, y: y + h/2 }

        if (topLeft.x < 0)
            vector.x = w/2;
        else if (bottomRight.x > window.innerWidth)
            vector.x = window.innerWidth - w/2;

        if (topLeft.y < 0)
            vector.y = h/2;
        else if (bottomRight.y > window.innerHeight)
            vector.y = window.innerHeight - h/2;


        // Translate the center point to the top left (for css)
        vector.x -= menuWidth/2;
        vector.y -= menuHeight/2;

        menu.style.left = `${vector.x.toFixed(2)}px`;
        menu.style.top = `${vector.y.toFixed(2)}px`;
    }


    async close(event)
    {
        let {animateAsync, fadeOut} = SharpAssetsKit.animation;

        // This piece of code is here to check if the event
        // isn't fired by a right-click event or a selected option event
        // we want close it when the mouse is OUT
        if (event)
        {
            if (event.target != event.currentTarget)
                return;
            if (event.x < 0 || event.y < 0)
                return;

            if (event.target.classList.contains("menu"))
            {
                let { clientX, clientY } = event;
                let box = event.target.getBoundingClientRect();

                if ((box.x <= clientX && clientX <= box.x+box.width )
                && (box.y <= clientY && clientY <= box.y+box.height))
                    return;
            }
        }

        this.opened = false;

        await animateAsync(this.menu, AssetsKitMenu.MENU_SLIDE_OUT);
        this.menu.style.display = "none";
        if (this.isMobile)
            await fadeOut(AssetsKitMenu.menuBackgroundForMobile);

        this.menu.dispatchEvent(new Event("closed"));

        if (this.observer)
            this.observer.disconnect();
    }

    async openForMobile()
    {
        let {animateAsync, fadeIn} = SharpAssetsKit.animation;

        this.isMobile = true;
        this.menu.classList.add("mobile");
        document.body.appendChild(this.menu);

        await fadeIn(AssetsKitMenu.menuBackgroundForMobile);
        this.menu.style.display = "flex";
        await animateAsync(this.menu, AssetsKitMenu.MENU_SLIDE_IN);
    }

    async open()
    {
        let {menu} = this;
        let {animateAsync} = SharpAssetsKit.animation;

        menu.style.visibility = "hidden";
        menu.style.display = "flex";
        this.resizeAndPosition();

        menu.style.visibility = "visible";
        await animateAsync(menu, AssetsKitMenu.MENU_SLIDE_IN);

        if (!menu.hasAttribute("locked"))
            menu.onmouseleave = evt => this.close(evt);

        this.observer = new ResizeObserver(this.resizeAndPosition.bind(this))
        this.observer.observe(menu);
    }

    constructor(menu, button, direction)
    {
        this.menu = menu;
        this.button = button;
        this.direction = direction;
        this.name = menu.getAttribute("name");

        if (SharpAssetsKit.lang.isMobile())
            this.openForMobile();
        else
            this.open();

        this.menu.dispatchEvent(new Event("opened"));
    }
}





















declareNewBridge("menu", {

    registeredMenus: [],
    openedMenuMap: {},
    lastOpenedMenu: null,

    selectorToElement: function(selector)
    {
        if (selector)
        {
            if ('innerHTML' in selector)
                return selector;
            let menu = document.querySelector(selector);
            if (menu)
                return menu
        }
        throw new Error("Cannot find menu with selector : " + selector);
    },

    genericOpenMenu: async function(selector, button, direction)
    {
        let menu = this.selectorToElement(selector);
        document.body.appendChild(menu);

        let name = menu.getAttribute("name") ?? menu.getAttribute("id");

        let opened = null;
        if (opened = this.openedMenuMap[name] ?? false)
        {
            if (opened.opened)
                await opened.close();
            this.openedMenuMap[name] = undefined;
        }

        let instance = new AssetsKitMenu(menu, button, direction);
        this.lastOpenedMenu = instance;

        if (name)
            this.openedMenuMap[name] = instance;
    },

    open: function(selector, button, direction=null)
    {
        button = this.selectorToElement(button);
        this.genericOpenMenu(selector, button, direction);
    },

    openAtCoord: function(selector, x, y, direction="bottom")
    {
        button = {x,y};
        this.genericOpenMenu(selector, button, direction);
    },


    close: function()
    {
        return this.lastOpenedMenu?.close();
    },


    addMenuListeners: function()
    {
        document.querySelectorAll("[menu]").forEach(button => {
            if (this.registeredMenus.includes(button))
                return;
            this.registeredMenus.push(button);
            button.addEventListener("click", ()=>this.open(document.getElementById(button.getAttribute("menu")), button))
        });
        document.querySelectorAll(".menu").forEach(menu => {
            menu.style.display = "none";
        });
    },


    isOpened: function(menu)
    {
        return AssetsKitMenuPool.isMenuOpened(menu);
    }

}, menu => {return {
    selectorToElement: menu.selectorToElement,
    openMenu: menu.open,
    openMenuAtCoord: menu.openAtCoord,
    closeMenu: menu.close,
    addMenuListeners: menu.addMenuListeners,
    isMenuOpened: menu.isOpened
}})

document.addEventListener("DOMContentLoaded", SharpAssetsKit.menu.addMenuListeners);





document.addEventListener("DOMContentLoaded", ()=> {
    // This block place a background that is displayed when opening a menu on mobile
    // It darken what's behind the menu, and close it when pressed
    if (!SharpAssetsKit.lang.isMobile())
        return;

    let mb = document.createElement("section")
    mb.classList = "menu-mobile-background";
    mb.style.display = "none";
    document.body.appendChild(mb);

    AssetsKitMenu.menuBackgroundForMobile = mb;
    mb.addEventListener("click", SharpAssetsKit.menu.close);
})











