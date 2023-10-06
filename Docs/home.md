# 📚 Sharp-PHP Documentation

The goals behind Sharp are :
1. Make a clean code that is intuitive
2. Remove unecessary abstraction layers
3. Don't hide the code behind Facades objects
4. Make a Framework that doesn't break your IDE
5. Let you code apps quickly without worrying about setup/build
6. Have as few dependencies as possible (So far, only [PHPUnit](https://phpunit.de/) is required)
7. Automate tedious task (like model creation)

You can browse this documentation with an IDE like Visual Studio Code with the markdown preview,
or in GitHub directly

## Summary

Even though this documentation should be enough to get started, don't hesitate to checkout the framework source code, it is a good way to learn about it too

Core
- 📦 [App directory & Configuration](./core/config.md)
- 🧩 [Understanding Sharp components](./core/components.md)
- 💻 [CLI command](./core/commands.md)
- 📃 [Logging](./core/logging.md)
- 🔏 [Session](./core/session.md)
- 🌐 [Code Helpers](./core/helpers.md)
- 💬 [Q/A & Snippets](./extras/snippets.md)

Core (Advanced):
- [📁 Storage](./env/storage.md)
- [🔥 Cache](./env/cache.md)
- [🪝 Events](./core/events.md)
- [🚃 ObjectArray class](./core/object-array.md)

Routing & Logic
- 🛣️ [Routing](./logic/routing.md)
- ⚙️  [Controllers](./logic/controllers.md)
- 🚦 [Middlewares](./logic/middlewares.md)
- 📨 [Requests & Responses](./logic/requests-and-responses.md)

Database
- 📚 [Database and Models](./data/database.md)

Business Logic
- 🥤 [Session Straw](./extras/session-straw.md)

Extras
- ⭐️ [Serving assets](./extras/assets.md)
- 🚘 [Automatic CRUD API](./extras/autobahn.md)
- 🔐 [Authentication](./security/authentication.md)
