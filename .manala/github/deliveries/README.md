# Deliveries

## Prerequisites

In order for `on.inputs.tier.type = 'environment'` to work, you need to have your environments configured in your
repository (https://github.com/<org>/<repo>/settings/environments).
Most common environments are `production` and `staging`, but you might have multiple stages as well (`staging-1`, `staging-2`, …)

## Usage

### Release Workflow

`.github/workflows/release.yaml`:

```yaml
name: Release
run-name: ${{ format('Release{0} on {1}', github.event.inputs.deploy == 'true' && ' & Deploy' || '', github.event.inputs.tier) }}

on:
  workflow_dispatch:
    inputs:
      tier:
        description: Tier
        type: environment
        required: true
      ref:
        description: Git reference. Provide an explicit ref to release if it does not match your tier.
        required: false
      deploy:
        description: Follow with a deployment if release succeeded
        type: boolean
        default: 'false'
        required: false

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}-${{ github.event.inputs.tier }}
  cancel-in-progress: true

jobs:
  release:
    name: ${{ format('Release on {0}', github.event.inputs.tier) }}
    runs-on: ubuntu-latest
    steps:
      - name: 'Checkout'
        uses: actions/checkout@v3

      - name: 'Release'
        uses: ./.manala/github/deliveries
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}
          ref: ${{ github.event.inputs.ref }}
          release: true
          #env: |
          #  SENTRY_AUTH_TOKEN=${{ secrets.SENTRY_AUTH_TOKEN }}
          #  COMPOSER_AUTH='{ "github-oauth": { "github.com": "${{ secrets.COMPOSER_AUTH_TOKEN }}" } }'

      - name: 'Trigger deployment workflow'
        if: success() && github.event.inputs.deploy == 'true'
        env:
          GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
          gh workflow run deploy \
              --field tier=${{ github.event.inputs.tier }}
```

### Deploy Workflow

`.github/workflows/deploy.yaml`:

```yaml
name: Deploy
run-name: ${{ format('Deploy on {0}', github.event.inputs.tier) }}

on:
  workflow_dispatch:
    inputs:
      tier:
        description: Tier
        type: environment
        required: true
      ref:
        description: Git reference from the release repository. Do only provide to deploy another reference than the latest available version for the tier (deploy a previous release or a specific commit).
        required: false

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}-${{ github.event.inputs.tier }}
  cancel-in-progress: true

jobs:
  deploy:
    name: ${{ format('Deploy on {0}', github.event.inputs.tier) }}
    runs-on: ubuntu-latest

    environment:
      name: ${{ github.event.inputs.tier }}
      url: ${{ steps.deploy.outputs.deployment_url }}

    steps:
      - name: 'Checkout'
        uses: actions/checkout@v3

      - name: 'Deploy'
        uses: ./.manala/github/deliveries
        id: deploy
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}
          release: false
          deploy: true
          deploy_ref: ${{ github.event.inputs.ref }}
```

## Exposing secrets

Read more about [exposing secrets to Manala actions](../env/README.md).
