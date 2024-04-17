# 🧊 sharp-php

> [!IMPORTANT]
> This project is still under development

Sharp is a Framework for PHP 8 that focuses on code cleanliness and simplicity

The goal is to have a good balance between abstraction and concrete objects

## 📚 Documentation and Tutorials

You can find resources to work/learn with Sharp in the [Docs directory](./Docs)

## 📦 Create a project

```bash
mkdir MyProject
cd MyProject

git init
git submodule add https://github.com/yonis-savary/sharp-php.git Sharp

# linux
cp -r Sharp/Core/Server/* .
# windows
xcopy /s Sharp/Core/Server/* .

php do fill-configuration
php do create-application MyProject

php do serve
```

This script :
1. Copy Sharp and its Test suite
2. Copy the `Public` directory and `do` script
3. Create an empty application named `MyProject`


Your directory will look like
- `MyProject/`
- `Public/`
- `Sharp/`
- `.git/`
- `do`
- `sharp.json`
- `.gitmodules`


## Release's features

- 🟢 - tested feature
- 🔵 - tested feature (some edge-case tests may be missing)
- 🟡 - untested feature

🤖 Core
- [x] 🟢 Configuration (JSON)
- [x] 🟢 Caching
- [x] 🟢 Logging
- [x] 🟢 Events
- [x] 🔵 CLI Commands (Base utilities commands)
- [x] 🟢 Tests


🌐 Web
- [x] 🔵 Session
- [x] 🟢 Request / Responses
- [x] 🟢 Controllers
- [x] 🔵 Renderer
- [x] 🟢 Routing / Middlewares

💾 Data
- [x] 🟢 Database
- [x] 🟢 Models
- [x] 🟢 FTP Support (🌟 New !)
- [x] 🟢 Queues support (🌟 New !)

🔐 Security
- [x] 🟢 Authentication
- [x] 🟢 CSRF

🎉 Extras
- [x] 🟢 Asset serving
- [x] 🟢 Model CRUD API
