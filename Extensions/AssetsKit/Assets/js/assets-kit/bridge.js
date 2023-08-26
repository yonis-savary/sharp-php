class Bridge
{
    constructor(object={})
    {
        return new Proxy(object, {
            set: function(target, key, value) {
                if (typeof target[key] !== "undefined")
                    throw new Error("Cannot reassign existing key !");

                target[key] = value;
                return true;
            },
            get: function(target, key) {
                let toReturn = target[key];
                if (typeof toReturn == "function")
                    toReturn = toReturn.bind(target);
                return toReturn;
            }
        })
    }
}

const SharpAssetsKit = new Bridge( {utils:{
    ...{
        allowPrototypesMutation: true,
        allowGlobalFunctions: true,

        alterPrototype: function(callback, toPass=null){
            if (this.allowPrototypesMutation)
                callback(toPass)
        },
        declareGlobals: function(object){
            if (!this.allowGlobalFunctions)
                return;

            for (let [key, callback] of Object.entries(object))
            {
                if (typeof window[key] === "undefined")
                    window[key] = callback;
                else
                    console.warn(`Cannot redeclare [key]`);
            }
        }
    },
    ...(typeof SharpAssetsKitOptions !== "undefined" ? SharpAssetsKitOptions: {})
}});

function declareNewBridge(name, object, globalsToDeclare=null, prototypeMutator=null)
{
    SharpAssetsKit[name] = new Bridge(object);

    //console.debug(globalsToDeclare(SharpAssetsKit[name]))

    if (typeof globalsToDeclare == "function")
        SharpAssetsKit.utils.declareGlobals(globalsToDeclare(SharpAssetsKit[name]));

    if (typeof prototypeMutator == "function")
        SharpAssetsKit.utils.alterPrototype(prototypeMutator, SharpAssetsKit[name]);
}