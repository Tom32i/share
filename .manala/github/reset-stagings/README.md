# Reset stagings

## Usage

In order to properly detect and notify PRs already merged in a staging branch,
you might want to add the following labels to your GitHub repository:

* `staging`
* `staging-reset`

Create the `.github/workflows/reset-stagings.yaml` workflow file:

```yaml
name: Reset stagings
run-name: 'Reset staging branch'

on:
  workflow_dispatch:
    inputs:
      ref:
        description: Git reference.
        default: master
        required: true
      comment:
        description: Add a comment to notify opened PRs
        type: boolean
        default: 'true'
        required: false
      deploy:
        description: Re-release and deploy the branches with the new code
        type: boolean
        default: 'false'
        required: false

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:

  reset:
    name: 'Reset staging branch'
    runs-on: ubuntu-latest
    env:
      GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}

    steps:
      - name: 'Checkout'
        uses: actions/checkout@v4

      - name: 'Reset hard staging branch on ${{ github.event.inputs.ref }}'
        uses: nicksnell/action-reset-repo@master
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          base_branch: ${{ github.event.inputs.ref }}
          reset_branch: staging

      - name: 'Find PRs with label staging'
        id: find-prs
        run: |
          prs=$(gh pr list --label 'staging' --json=number --state open | jq -cr '.|map(.number)')
          echo $prs
          echo $prs | jq .
          echo "prs=$prs" >> $GITHUB_OUTPUT

      - name: 'Labelize opened PRs with label staging'
        run: |
          echo ${{ steps.find-prs.outputs.prs }} | jq -c '.[]' | while read pr; do
            gh pr view $pr --json=number,title || true
            gh pr edit $pr --remove-label staging || true
            gh pr edit $pr --add-label staging-reset || true
          done

      - name: 'Notify opened PRs with label staging'
        if: success() && github.event.inputs.comment == 'true'
        run: |
          echo ${{ steps.find-prs.outputs.prs }} | jq -c '.[]' | while read pr; do
            gh pr view $pr --json=number,title
            gh pr comment $pr --body "> **Warning** la branche \`staging\` a été reset. Cette PR doit à nouveau être mergée sur \`staging\`"
          done

  deploy:
    name: 'Re-release & deploy staging branch'
    if: success() && github.event.inputs.deploy == 'true'
    needs: reset
    runs-on: ubuntu-latest
    env:
      GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}

    steps:
      - name: 'Checkout'
        uses: actions/checkout@v4

      - name: 'Trigger release workflow for staging'
        run: |
          gh workflow run release \
              --field deploy=true \
              --field tier=staging
```
