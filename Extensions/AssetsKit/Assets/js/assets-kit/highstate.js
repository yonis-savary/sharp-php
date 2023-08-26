/*
 _  _ _      _       _        _
| || (_)__ _| |_  __| |_ __ _| |_ ___
| __ | / _` | ' \(_-<  _/ _` |  _/ -_)
|_||_|_\__, |_||_/__/\__\__,_|\__\___|
       |___/
--------------------------------------

AssetsKitHighstate is a script that can help you improve
your by making "states chip" more beautiful

___________________________________

Example Context:

Let's say you're making an application that manage payments,
which may have "status" like "Pending", "Paid" or "Cancelled"

Now let's say you made a <table> that holds multiples payments inside,
with a "status" column.
Now, to make it more readable and less boring, you decide to highlight
thoses "status" cell with a color matching their state

This script can help you accomplish this highlighting / transmformation !

(You can read the code, then see the example at the end of this file)

*/


/**
 * Preset that holds multiples possible state
 * Each state got a name, a svg, and a color
 */
class AssetsKitHighstatePreset
{
    states = [];
    name = "unknown"

    constructor (name)  { this.name = name; }
    getStates()         { return this.states; }
    getName()           { return this.name; }

    addState(name, color=null, svg=null)
    {
        this.states.push({ name: `${name}`, color, svg });
        return this;
    }
}



/**
 * AssetsKitHighstate is the static class that can help you
 * highlight your elements with a preset
 */
class AssetsKitHighstate
{
    static presets = [];
    static presetsNames = new Set();
    static transformCallback = AssetsKitHighstate.transform;

    static registerPreset(...presets)
    {
        presets.forEach(preset => {
            if (!preset instanceof AssetsKitHighstatePreset)
                return console.warn("preset must be an instance of AssetsKitHighstatePreset !");

            let name = preset.getName();

            if (this.presetsNames.has(name)){
                console.warn(`Preset [${name}] already registered, replacing old.`);
                this.presets = this.presets.filter(x => x.getName() != name);
            }

            this.presetsNames.add(name);
            this.presets.push(preset);
        })
    }


    static setTransformCallback(callback)
    {
        this.transformCallback = callback;
    }

    /**
     * Customizable function ! Takes a DOM Element, a preset's state
     * And transform the element
     */
    static transform(element, state)
    {
        let style = state.color == null ? '': `style='background:${state.color}'`;
        let svgHTML = state.svg == null ? '' : svg(state.svg, 18);

        let newEl = `
        <span class="state-span svg-text" ${style}>
            ${svgHTML}
            <b>${element.innerHTML}</b>
        </span>
        `

        element.innerHTML = newEl
    }



    static highlightAll(selector, presetName)
    {
        let preset = this.presets.find(x => x.getName() == presetName);
        if (typeof preset == "undefined")
            throw new Error(`[${presetName}] preset not found !`);
        let states = preset.getStates();

        let elements = document.querySelectorAll(selector);

        elements.forEach( el => {
            let name = el.innerText.trim();
            let state = states.find(x => x.name == name);
            if (state == undefined)
                return;

            this.transformCallback(el, state);
        })
    }
}



SharpAssetsKit.utils.declareGlobals({
    Highstate: AssetsKitHighstate,
    HighstatePreset: AssetsKitHighstatePreset
});


class AssetsKitHighstateColors
{
    static RED      = "#dc200a";
    static ORANGE   = "#ee6907";
    static GREEN    = "#13b259";
    static BLUE     = "#0fa2d5";
    static PURPLE   = "#7723e3";

    static GRAY     = "#929292";
}




/*
  ___ _   _ ___ _____ ___  __  __
 / __| | | / __|_   _/ _ \|  \/  |
| (__| |_| \__ \ | || (_) | |\/| |
 \___|\___/|___/ |_| \___/|_|  |_|

If you want to customize AssetsKitHighstate behavior, you can simply
change the way it transform DOM elements

```js
    AssetsKitHighstate.setTransformCallback(
        function(element, state)
        {
            // State got these properties
            // - name  : State label
            // - svg   : Svg name
            // - color : Color string
        }
    )
```
*/




/*
 ___  ___   ____  __ ___ _  _ _____ ___
| _ \/_\ \ / /  \/  | __| \| |_   _/ __|
|  _/ _ \ V /| |\/| | _|| .` | | | \__ \
|_|/_/ \_\_| |_|  |_|___|_|\_| |_| |___/

It is advised to have read the example
context at the top of this file before continuing

Here is how you can use AssetsKitHighstate to highlight
your payments status

First we have to make sure our cells are selectable with css,
let's say those have the `status` html attribute

Then, we have to register somewhere our differents states
(Its only have to be registered once)

```js
    AssetsKitHighstate.registerPreset(
        new AssetsKitHighstatePreset("PaymentStatus")
        .addState("Pending"     , AssetsKitHighstateColors.BLUE, "hourglass-split")
        .addState("Paid"        , AssetsKitHighstateColors.GREEN, "check")
        .addState("Cancelled"   , AssetsKitHighstateColors.GRAY, "dash"),
    )
```

Finally, when our table is ready, we highlight those elements

```js
    AssetsKitHighstate.highlightAll("table [status]", "PaymentStatus")
```


function highstatePaymentExample()
{
    // This, only have to be called once in your code
    AssetsKitHighstate.registerPreset(
        new AssetsKitHighstatePreset("PaymentStatus")
        .addState("Pending"     , AssetsKitHighstateColors.BLUE, "hourglass-split")
        .addState("Paid"        , AssetsKitHighstateColors.GREEN, "check")
        .addState("Cancelled"   , AssetsKitHighstateColors.GRAY, "dash"),
    )

    let table = document.createElement("table")
    table.id = "myExampleTable"
    table.innerHTML = `
    <tr> <td>Payment           <td>Amount   <td>Status
    <tr> <td>11f7b77a1a629179  <td>5792.33  <td status="2">Paid
    <tr> <td>8f48a50452044120  <td> 591.52  <td status="1">Pending
    <tr> <td>cac9443e9c89101b  <td>4040.64  <td status="3">Cancelled
    <tr> <td>8d64d715ba18036c  <td>1552.86  <td status="1">Pending
    <tr> <td>a7e6e495c2e2c7fc  <td>  92.30  <td status="2">Paid
    <tr> <td>6a64b780da9c7a4f  <td>1428.90  <td status="1">Pending
    `

    document.body.appendChild(table);

    AssetsKitHighstate.highlightAll("#myExampleTable [status]", "PaymentStatus")
}
*/