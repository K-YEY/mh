Preview codemod patches

Run the preview script to produce dry-run patches for converting `shadcn-*` classes
to the lightweight `Ui*` components added under `resources/js/Components/UI`.

Usage:

```bash
# from repo root
# if package.json "type": "module", run the CJS script:
node scripts/preview-shadcn-codemod.cjs
```

Patches are written to `.codemod/preview/<path>.patch` and a summary JSON at
`.codemod/preview/summary.json`.

This is a conservative, best-effort preview. Review patches before applying.
