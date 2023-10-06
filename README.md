# 🧊 sharp-php

> [!IMPORTANT]
> This project is still under development

Sharp is a Framework for PHP 8 that focuses on code cleanliness and simplicity

The goal is to have a good balance between abstraction and concrete objects

## 📚 Documentation and Tutorials

You can find resources to work/learn with Sharp in the [Docs directory](./Docs/home.md)

## 🪄 Create a project

```bash
mkdir MyProject
cd MyProject

git init
# Clone this repository inside `Sharp` directory
git submodule add https://github.com/yonis-savary/sharp-php.git Sharp

# Copy Public directory and "do" script
cp -r Sharp/Core/Server/* .

php do serve
```
