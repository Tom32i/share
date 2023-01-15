# Exposing secrets

In case you need to expose secrets as env vars, for instance to provide an auth token during the release process,
you can use the `with.env` key to provide a string of env vars to be forwarded, as shown above.

Use Github [steps conditions](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions#jobsjob_idstepsif)
and the `./.manala/github/env` action in order to expose different secrets depending on the app / tier:

```yaml
jobs:
  release:
    # […]
    steps:
      # […]
      - name: Configure staging secrets
        uses: ./.manala/github/env
        if: ${{ github.event.inputs.tier == 'staging' }}
        with:
          env: SENTRY_AUTH_TOKEN=${{ secrets.STAGING_SENTRY_AUTH_TOKEN }}

      - name: Configure production secrets
        uses: ./.manala/github/env
        if: ${{ github.event.inputs.tier == 'production' }}
        with:
          env: SENTRY_AUTH_TOKEN=${{ secrets.production_SENTRY_AUTH_TOKEN }}

      - name: Release
        uses: ./.manala/github/deliveries
        with:
          secrets: ${{ toJSON(secrets) }}
          tier: ${{ github.event.inputs.tier }}
          ref: ${{ github.event.inputs.ref }}
          release: true
          deploy: ${{ github.event.inputs.deploy }}
```

> **Note**:
> The `integration` and `release` actions also support the `with.env` key directly for most simple usages:

```yaml
jobs:
  test:
    # […]
    steps:
      # […]
      - name: Test
        uses: ./.manala/github/integration
        with:
          env: |
            COMPOSER_AUTH='{ "github-oauth": { "github.com": "${{ secrets.COMPOSER_AUTH_TOKEN }}" } }'
```

```yaml
jobs:
  release:
    # […]
    steps:
      # […]
      - name: Release
        uses: ./.manala/github/deliveries
        with:
          # […]
          env: |
            SENTRY_AUTH_TOKEN=${{ secrets.SENTRY_AUTH_TOKEN }}
            COMPOSER_AUTH='{ "github-oauth": { "github.com": "${{ secrets.COMPOSER_AUTH_TOKEN }}" } }'
```

## Getting an OAuth token

Sometimes, the `secrets.GITHUB_TOKEN` might not be enough to perform some actions,
such as reading another private repository, since it only has permissions on the current repository.

In this regard, we use the GitHub OAuth [Workflow Knight](https://github.com/apps/workflows-knight) app registered
on the organization level, to generate a token with the required permissions, with an extra step:

```yaml
jobs:
  test:
    # […]
    steps:
      # […]
      - name: 'Get Workflows Knight app token'
        id: get_app_token
        uses: machine-learning-apps/actions-app-token@master
        with:
          APP_PEM: ${{ secrets.WORKFLOWS_KNIGHT_APP_PEM_BASE_64 }}
          APP_ID: ${{ secrets.WORKFLOWS_KNIGHT_APP_ID }}

      - name: Test
        uses: ./.manala/github/integration
        with:
          env: |
            COMPOSER_AUTH='{ "github-oauth": { "github.com": "${{ steps.get_app_token.outputs.app_token }}" } }'
```