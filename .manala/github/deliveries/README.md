# Deliveries

## Prerequisites

In order for `on.inputs.tier.type = 'environment'` to work, you need to have your environments configured in your
repository (https://github.com/<org>/<repo>/settings/environments).
Most common environments are `production` and `staging`, but you might have multiple stages as well (`staging-1`, `staging-2`, …)

## Usage

 Please now consult:
 - [./release/README.md](./release/README.md) for release workflow
 - [./deploy/README.md](./deploy/README.md) for deploy workflow
 - [env.md](./../integration/env.md) for exposing secrets through env vars
