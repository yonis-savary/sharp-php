/*
    component.js - Single-use component creator

    This script can help you improvise component for your page,
    here is an example

    ```js
    let object = Component( data => `
        <div>
            <button onclick="${data.method.decrement}">minus</button>
            <span>${data.html.count}</span>
            <button onclick="${data.method.increment}">plus</button>
        </div>
    `}, {
        count: 0,
        decrement: data => data.props.count--,
        increment: data => data.props.count++
    });

    document.body.appendChild(object);
    ```

    Note: methods can be accessed with `this.method.<propertyName>`

    Component's data can be accessed with 3 properties:
    - [this|param].data : Litteraly a reference to the data, the raw object
    - [this|param].props : A proxy that re-render the component when edited
    - [this|param].html : Another proxy that escape HTML that go through it !


*/

class AssetsKitComponent extends HTMLElement
{
    static BLOCK_NAME = "assetskit-component";
    static instances = {};

    static call(componentId, methodName, event)
    {
        let theInstance = AssetsKitComponent.instances[componentId] ?? null;

        if (!theInstance)
            throw new Error(`No component instance with id [${componentId}] found !`);

        let method = theInstance.data[methodName]
        method.bind(theInstance)(event);
    }

    static register(instance)
    {
        let id;

        do {
            id = instance.componentId = Date.now().toString(36) + "-" + Math.random().toString(36).slice(2);
        }
        while (id in AssetsKitComponent.instances);

        AssetsKitComponent.instances[id] = instance;
    }

    static new(callback, data)
    {
        let newComponent = document.createElement(AssetsKitComponent.BLOCK_NAME);
        newComponent.initialize(callback, data);
        return newComponent;
    }





    initialize(callback, data)
    {
        AssetsKitComponent.register(this);

        this.callback = callback.bind(this);

        // Raw object, can be edited at will
        this.data = Object.assign({}, data);

        // Proxy, re-render the component when edited
        this.props = new Proxy(this.data, {
            get: (target, key) => target[key],
            set: (target, key, value) => (target[key]=value, this.refresh(), true)
        });

        // "Protected" data, escape everything that go through
        // (Notice that the target is this.props)
        this.html = new Proxy(this.props, {
            get: (target, key) => escapeHTML(target[key]),
            set: (target, key, value) => target[key] = escapeHTML(value)
        })

        this.method = new Proxy(this.data, {
            get: (target, key) => {

                let theFunction = target[key];

                if (typeof theFunction !== "function")
                    throw new Error(`Only functions can be accessed through this.method`);

                return `AssetsKitComponent.call('${this.componentId}', '${theFunction.name}', event)`
            }
        })

        this.call = new Proxy(this.data, {
            get: (target, key) => {

                let theFunction = target[key];

                if (typeof theFunction !== "function")
                    throw new Error(`Only functions can be accessed through this.method`);

                return theFunction.bind(this);
            }
        })

        this.refresh();
    }


    refresh()
    {
        this.innerHTML = (this.callback)(this);
    }
}


customElements.define(AssetsKitComponent.BLOCK_NAME, AssetsKitComponent)

SharpAssetsKit.utils.declareGlobals({
    Component: AssetsKitComponent.new
});













/*
function componentExamples()
{
    document.body.innerHTML = ""

    let counter = Component( c =>`
        <div>
            <button onclick="${c.method.decrement}">-</button>
            <span>${c.html.count}</span>
            <button onclick="${c.method.increment}">+</button>
        </div>
    `, {
        count: 0,
        decrement: (c)=> c.props.count--,
        increment: (c)=> c.props.count++
    });

    document.body.appendChild(counter);



    let todoList = Component(c => `
        <span>
            <input type="text" placeholder="Todo next...">
            <button onclick="${c.method.appendTask}">Add</button>
        </span>
        <ul>
            ${c.data.list.map((x,i) => html`
                <li>
                    ${x}
                    <button onclick="${c.method.deleteIndex}" index="${i}">Delete</button>
                </li>`
            ).join("")}
        </ul>
    `, {
        list: [],
        appendTask: function(){
            let newValue = this.querySelector("input").value;
            this.props.list.push(newValue);
            this.refresh();
        },
        deleteIndex: function(_, event){
            let index = event.target.getAttribute("index");
            this.data.list = this.data.list.filter((_,i)=> i!=index);
            this.refresh();
        }
    })
    document.body.appendChild(todoList);
}
*/


//document.addEventListener("DOMContentLoaded", _ => componentExamples(document.body))