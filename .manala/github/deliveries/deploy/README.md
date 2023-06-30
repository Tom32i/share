# Deploy

## Usage

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

      - name: 'Setup container with Manala'
        uses: ./.manala/github/deliveries/setup
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}

      - name: 'Deploy'
        uses: ./.manala/github/deliveries/deploy
        id: deploy
        with:
          tier: ${{ github.event.inputs.tier }}
          ref: ${{ github.event.inputs.ref }}
```

## Exposing secrets

Read more about [exposing secrets to Manala actions](../env/README.md).
