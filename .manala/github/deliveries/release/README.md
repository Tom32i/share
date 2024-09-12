# Release

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
        uses: actions/checkout@v4

      - name: 'Setup container with Manala'
        uses: ./.manala/github/deliveries/setup
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}
          ref: ${{ github.event.inputs.ref }}
          #env: |
          #  SENTRY_AUTH_TOKEN=${{ secrets.SENTRY_AUTH_TOKEN }}
          #  COMPOSER_AUTH='{ "github-oauth": { "github.com": "${{ secrets.COMPOSER_AUTH_TOKEN }}" } }'

      - name: 'Release'
        uses: ./.manala/github/deliveries/release
        with:
          tier: ${{ github.event.inputs.tier }}

      - name: 'Trigger deployment workflow'
        if: success() && github.event.inputs.deploy == 'true'
        env:
          GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
          gh workflow run deploy \
              --field tier=${{ github.event.inputs.tier }}
```
