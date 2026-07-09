# Document Search Portal Proof Checklist

Use this checklist before promoting Document Search Portal as a stronger portfolio case study.

## Repository Proof

- [x] Public GitHub repository exists.
- [x] Main README explains setup, stack, API endpoints, and architecture.
- [x] Backend-specific documentation exists.
- [x] Frontend-specific documentation exists.
- [x] Solution/design document exists.
- [x] Case-study draft exists in `docs/CASE_STUDY.md`.
- [ ] Add screenshots to `docs/proof-assets/`.
- [ ] Link screenshots from the main README.
- [ ] Add repository topics on GitHub.
- [ ] Add a concise GitHub repository description.

## Functional Proof

- [ ] Backend dependencies install successfully with `composer install`.
- [ ] Frontend dependencies install successfully with `npm install`.
- [ ] Database migration runs successfully.
- [ ] Backend API starts locally.
- [ ] Frontend starts locally.
- [ ] Sample TXT document uploads successfully.
- [ ] Sample PDF document uploads successfully.
- [ ] Document list returns uploaded files.
- [ ] Document details page opens.
- [ ] Search returns relevant results.
- [ ] Search result highlighting is visible.
- [ ] Search suggestions appear while typing.
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

- [ ] Upload screen screenshot.
- [ ] Search results screenshot.
- [ ] Search suggestions screenshot.
- [ ] Document detail screenshot.
- [ ] API test output snippet.
- [ ] Architecture diagram image or Mermaid diagram.
- [ ] 60-second project explanation.

## Case Study Quality Gate

- [ ] Problem is clear to non-technical readers.
- [ ] Stack is grouped cleanly.
- [ ] Personal contribution is explicit.
- [ ] Technical decisions are explained.
- [ ] Proof assets are visible.
- [ ] Limitations or future improvements are honest.
