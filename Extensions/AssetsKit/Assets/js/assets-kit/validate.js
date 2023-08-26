/*
__   ___   _    ___ ___   _ _____ ___ ___  _  _
\ \ / /_\ | |  |_ _|   \ /_\_   _|_ _/ _ \| \| |
 \ V / _ \| |__ | || |) / _ \| |  | | (_) | .` |
  \_/_/ \_\____|___|___/_/ \_\_| |___\___/|_|\_|

This parts allow you to have a better control over your form and data

How to use it ?

First, we have the read() function, which takes an input element, and return
and object with multiples method that can help you validate the input's value

```js

let object = read(myNumberInput) // If the input has type=number, its value is automatically parsed
.between(0, 100) // Only for numbers, min and max are optionnal

object.value // Get the input's value
object.valid // Do the value respect our requirements ?

read(myTextInput)
.nullable(false, 'myDefaultValue')
.match(/^Hello/)
.respect(value => value == value.toUpperCase()) // Custom condition
.transform(value => value.toLowerCase()) // Manually transform the value

```

Validate a form

You can validate your form with the validate() function,
which takes an object and two callbacks as parameters

```js
validate({
    text: read(myText).match(/abc/).error("The text must contain 'abc'"),
    number: read(myNumber).between(-5, 5).error('The number must be between -5 and 5')
}, (inputs, message)=>{
    // Something went wrong ! inputs parameter contains the inputs at fault
    // message is a pre-made error message made from .error() calls above
}, (inputs)=>{
    // Everything is right ! Here is an example of what inputs can look like
    // { text: 'hello abc !', number: 3 }
    // The object's keys are the ones described when calling validate()
    // The object's values are taken from the inputs
})
```


API :
error(msg)    : Define a error message for the USER
notNull       : Make an error on null value
nullable()    : Make null value valid (A default value can be given to replace a null one)
between(A,B)  : Is the input a value between A and B valid
match         : Must match a regex
respect       : Must respect a callback return value
transform     : Transform input's value

*/

declareNewBridge("validate", {

    VALIDATE_DISPLAY_ERROR: false,

    read: function (input) {
        if (typeof input.value == "undefined" )
            throw new Error("Given input is not readable");

        let type = input.getAttribute("type") ?? "text";
        let value;

        switch (type.toLowerCase())
        {
            case 'checkbox':
            case 'radio':
                value = input.checked;
                break;
            default:
                value = input.value.trim();
                break;
        }

        if (value === "")
            value = null;

        if (type == "number" && value !== null)
            value = parseFloat(value);

        return {
            errorLabel: 'This input is needed',
            errorElement: null,
            input: input,
            valid: true,
            type: type,
            value : value,

            error: function(label){
                this.errorLabel = label
                return this;
            },

            notNull: function(){
                this.valid &= (this.value !== null)
                this.value ??= ""; // If you are using transform on a non-null object, we are assuming the value is not null
                return this;
            },

            nullable : function(defaultValue=null){
                if (defaultValue)
                    this.value ??= defaultValue;

                return this;
            },

            between : function(min=null, max=null, inclusive=true){
                if (min === null && max === null)
                    return this;

                if (min === null && max !== null)
                    this.valid &= inclusive ?
                        this.value <= max :
                        this.value < max;
                else if (min !== null && max === null)
                    this.valid &= inclusive ?
                        min <= this.value :
                        min < this.value;
                else
                    this.valid &= (inclusive == true) ?
                        min <= this.value && this.value <= max:
                        max <  this.value && this.value <  max;

                return this;
            },

            match : function(regex){
                return this.respect(x => (x + "").match(regex)?.length ?? false)
            },

            respect : async function(callback, errorMessage=null){
                let res = await callback(this.value, this.input, this);
                this.valid &= res;
                if (!res)
                    this.errorLabel = errorMessage ?? this.errorLabel;
                return this;
            },

            removeError: function(){
                this.input.parentNode.querySelector(".validate-error")?.remove();
            },

            insertError: function(){
                this.removeError();
                this.errorElement = document.createElement("span")
                this.errorElement.classList = "text-red validate-error";
                this.errorElement.innerText = this.errorLabel;
                this.input.parentNode.appendChild(this.errorElement);
            },

            transform: function(mapper){
                this.value = mapper(this.value);
                return this;
            }
        }
    },


    validate: async function(inputs, onSuccess, onError=null){

        onError ??= function(inputs, message){
            console.error(message);
            if (this.VALIDATE_DISPLAY_ERROR)
                alert(message);
        };

        let valid = true;
        let causes = [];
        let values = {}

        for (let key of Object.keys(inputs))
        {
            let input = inputs[key] = await inputs[key];
            valid &= input.valid;

            if (input.valid == false)
                causes.push(input);
            else
                values[key] = input.value;
        }

        if (valid == true)
        {
            Object.values(inputs).forEach(x => x.removeError());
            onSuccess(values);
        }
        else
        {
            causes.forEach(x => x.insertError());
            onError(causes, causes.map(x => ` - ${x.errorLabel}`).join("\n"));
        }
    }
}, form => {return {
    read: form.read,
    validate: form.validate
}});
