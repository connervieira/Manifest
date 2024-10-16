# Manifest

A remote Predator license plate list source.


## Description

[Predator ALPR](https://v0lttech.com/predator.php) supports hot-lists and ignore-lists, which are a powerful way to control how the system handles certain license plates when they are detected. A hot-list is a list of plates that will trigger a heightened alert. Conversely, an ignore-list is a list of plates that will be ignored, and omitted from logs. Predator supports the ability to load hot-lists and ignore-lists from remote sources, such that the latest lists are automatically fetched from a remote server when the system starts. This allows a single source to be automatically distributed to multiple Predator clients.

Manifest is conveinent web-service for managing and distributing license plate lists to Predator clients. Manifest allows an arbitrary number of users to create both ignore-lists and hot-lists through an intuitive web interface. Manifest also makes it easy to users to add their lists to Predator as remote sources. Admins have the ability to control who can access their Manifest instance, as well as how many license plates users can add to their personal license plate lists.


## Features

### Robust Permissions

Manifest allows administrators to configure permissions regarding who can access their instance, how many lists they can create, and how many plates each list can contain. Permissions can be set globally, or on an individual basis.

### Multi-List Support

Manifest allows each user to create multiple license plate lists (so long as the permissions allow for it), making it easy to manage multiple devices with multiple purposes.

### Offline Capable

While Manifest is a network utility, it does not depend on an internet connection to function. It only needs a connection to the Predator instances you want to communicate with.
