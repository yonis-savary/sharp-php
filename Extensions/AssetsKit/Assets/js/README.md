# AssetsKit Javascript

This directory contains :

| Script            | Purpose                       |
|-------------------|-------------------------------|
| `utils.js`        | Various language utilities    |
| `validation.js`   | Form Validation               |
| `animation.js`    | Async animation, fade in/out  |
| `date.js`         | Date utility                  |
| `fetch.js`        | AutoBahn component API utils  |
| `svg.js`          | SVG component utils           |
| `workers.js`      | Async utilities               |
| `aside.js`        | Aside menu utils              |
| `modal.js`        | Modal Sections                |
| `autocomplete.js` | Aucomplete utils              |
| `overlay.js`      | Overlay utils                 |
| `highstate.js`    | this script can help you improve the presentation of your data states |


The documentation is written inside every scripts,

## Dependencies Tree


| Module            | Dependencies               |
|-------------------|----------------------------|
| `utils.js`        | ---                        |
| `validation.js`   | ---                        |
| `animation.js`    | ---                        |
| `date.js`         | ---                        |
| `svg.js`          | ---                        |
| `workers.js`      | ---                        |
| `fetch.js`        | `utils.js`                 |
| `aside.js`        | `utils.js`, `animation.js` |
| `modal.js`        | `utils.js`, `animation.js` |
| `autocomplete.js` | `utils.js`, `modal.js`     |
| `highstate.js`    | `svg.js`                   |
| `overlay.js`      | `animation.js`             |