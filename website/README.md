# Orkestra Documentation

This website is built using [Docusaurus 2](https://docusaurus.io/), a modern static website generator.

## Installation

```
npm install
```

## Local Development

```
npm start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

## Build

```
npm run build
```

This command generates static content into the `build` directory and can be served using any static contents hosting service.

## Deployment

The documentation is automatically deployed to GitHub Pages when:
1. A new release tag is pushed (v*)
2. Changes are made to the main branch with '[update-docs]' in the commit message
3. Changes are made to the docs folder in the main branch

The deployment is handled by GitHub Actions. 