# Deliveries

## Usage

`.github/workflows/release.yaml`:

```yaml
name: Release

on:
  workflow_dispatch:
    inputs:
      tier:
        description: Tier
        type: choice
        options: [production, staging]
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
  group: ${{ github.workflow }}-${{ github.ref }}${{ github.event.inputs.app != '' && format('-{0}', github.event.inputs.app) || '' }}-${{ github.event.inputs.tier }}
  cancel-in-progress: true

jobs:
  release:
    name: Release ${{ github.event.inputs.app != '' && format('{0}@', github.event.inputs.app) || '' }}${{ github.event.inputs.tier }}
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v3
      - name: Release
        uses: ./.manala/github/deliveries
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}
          ref: ${{ github.event.inputs.ref }}
          release: true
          deploy: ${{ github.event.inputs.deploy }}
```

`.github/workflows/deploy.yaml`:

```yaml
name: Deploy

on:
  workflow_dispatch:
    inputs:
      tier:
        description: Tier
        type: choice
        options: [production, staging]
        required: true
      ref:
        description: Git reference from the release repository. Do only provide to deploy another reference than the latest available version for the tier (deploy a previous release or a specific commit).
        required: false

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}${{ github.event.inputs.app != '' && format('-{0}', github.event.inputs.app) || '' }}-${{ github.event.inputs.tier }}
  cancel-in-progress: true

jobs:
  deploy:
    name: Deploy ${{ github.event.inputs.app != '' && format('{0}@', github.event.inputs.app) || '' }}${{ github.event.inputs.tier }}
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v3
      - name: Deploy
        uses: ./.manala/github/deliveries
        with:
          secrets: ${{ toJSON(secrets) }}
          app: ${{ github.event.inputs.app }}
          tier: ${{ github.event.inputs.tier }}
          release: false
          deploy: true
          deploy_ref: ${{ github.event.inputs.ref }}
```
