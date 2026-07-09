# Document Search Portal Proof Checklist

Use this checklist before promoting Document Search Portal as a stronger portfolio case study.

## Repository Proof

- [x] Public GitHub repository exists.
- [x] Main README explains setup, stack, API endpoints, and architecture.
- [x] Backend-specific documentation exists.
- [x] Frontend-specific documentation exists.
- [x] Solution/design document exists.
- [x] Case-study draft exists in `docs/CASE_STUDY.md`.
- [x] Add screenshots to `docs/proof-assets/`.
- [x] Link screenshots from the main README.
- [x] Add local verification log.
- [ ] Add repository topics on GitHub.
- [ ] Add a concise GitHub repository description.

## Functional Proof

- [x] Backend dependencies are present locally and Composer validation passes.
- [x] Frontend dependencies are present locally.
- [ ] Database migration runs successfully from a fresh database.
- [x] Backend API starts locally.
- [x] Frontend starts locally.
- [x] Sample TXT document uploads successfully.
- [ ] Sample PDF document uploads successfully.
- [x] Document list returns uploaded files.
- [ ] Document details page opens.
- [x] Search returns relevant results.
- [x] Search result highlighting is visible.
- [x] Search suggestions appear while typing.
- [ ] Document download works.
- [ ] Document delete works.

## Verification Commands

```bash
cd backend
composer install
./migrate.sh
php -S localhost:8000 -t public
```

```bash
cd backend
./test-api.sh
```

```bash
cd frontend
npm install
npm run lint
npm run build
npm start
```

## Portfolio Assets Needed

- [x] Upload/list screen screenshot.
- [x] Search results screenshot.
- [x] Search suggestions screenshot.
- [ ] Document detail screenshot.
- [x] API test output summary.
- [x] Architecture diagram as Mermaid in `docs/CASE_STUDY.md`.
- [ ] 60-second project explanation.

## Case Study Quality Gate

- [ ] Problem is clear to non-technical readers.
- [ ] Stack is grouped cleanly.
- [ ] Personal contribution is explicit.
- [ ] Technical decisions are explained.
- [x] Proof assets are visible.
- [ ] Limitations or future improvements are honest.
