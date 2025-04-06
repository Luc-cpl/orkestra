CURRENT_VERSION := $(shell grep -o '"version": *"[^"]*"' composer.json | sed 's/"version": *"v*\([^"]*\)"/\1/' || echo "0.0.0")

ifeq ($(shell echo $(CURRENT_VERSION) | grep -E "^[0-9]+\.[0-9]+\.[0-9]+$$"),)
$(warning Invalid version format detected: "$(CURRENT_VERSION)", using 0.0.0 as fallback)
CURRENT_VERSION := 0.0.0
endif

VERSION_PARTS := $(subst ., ,$(CURRENT_VERSION))
MAJOR := $(word 1,$(VERSION_PARTS))
MINOR := $(word 2,$(VERSION_PARTS))
PATCH := $(word 3,$(VERSION_PARTS))

.PHONY: tag-debug
tag-debug:
	@echo "Raw grep result: $$(grep -o '\"version\": *\"[^\"]*\"' composer.json || echo 'Not found')"
	@echo "Extracted version: $(CURRENT_VERSION)"
	@echo "Major: $(MAJOR)"
	@echo "Minor: $(MINOR)"
	@echo "Patch: $(PATCH)"

.PHONY: tag-current
tag-current:
	@echo "Checking git branch..."
	@if [ "$$(git rev-parse --abbrev-ref HEAD)" != "main" ]; then echo "Error: Not on main branch"; exit 1; fi
	@echo "Pulling latest changes..."
	@git pull
	@echo "Retagging current version $(CURRENT_VERSION)..."
	@git tag -d v$(CURRENT_VERSION) 2>/dev/null || true
	@git push origin :refs/tags/v$(CURRENT_VERSION) 2>/dev/null || true
	@git tag -a v$(CURRENT_VERSION) -m "Version $(CURRENT_VERSION)"
	@git push --tags
	@echo "Version v$(CURRENT_VERSION) retagged and pushed to GitHub"

.PHONY: tag-patch
tag-patch:
	@echo "Checking git branch..."
	@if [ "$$(git rev-parse --abbrev-ref HEAD)" != "main" ]; then echo "Error: Not on main branch"; exit 1; fi
	@echo "Pulling latest changes..."
	@git pull
	@echo "Creating new patch version..."
	$(eval NEW_PATCH := $(shell expr $(PATCH) + 1))
	$(eval NEW_VERSION := $(MAJOR).$(MINOR).$(NEW_PATCH))
	@echo "New version: $(NEW_VERSION)"
	@sed -i 's/"version": *"[^"]*"/"version": "v$(NEW_VERSION)"/' composer.json
	@git add composer.json
	@git commit -m "Bump version to $(NEW_VERSION)"
	@git tag -a v$(NEW_VERSION) -m "Version $(NEW_VERSION)"
	@git push && git push --tags
	@echo "Patch version v$(NEW_VERSION) created and pushed to GitHub"

.PHONY: tag-minor
tag-minor:
	@echo "Checking git branch..."
	@if [ "$$(git rev-parse --abbrev-ref HEAD)" != "main" ]; then echo "Error: Not on main branch"; exit 1; fi
	@echo "Pulling latest changes..."
	@git pull
	@echo "Creating new minor version..."
	$(eval NEW_MINOR := $(shell expr $(MINOR) + 1))
	$(eval NEW_VERSION := $(MAJOR).$(NEW_MINOR).0)
	@echo "New version: $(NEW_VERSION)"
	@sed -i 's/"version": *"[^"]*"/"version": "v$(NEW_VERSION)"/' composer.json
	@git add composer.json
	@git commit -m "Bump version to $(NEW_VERSION)"
	@git tag -a v$(NEW_VERSION) -m "Version $(NEW_VERSION)"
	@git push && git push --tags
	@echo "Minor version v$(NEW_VERSION) created and pushed to GitHub"

.PHONY: tag-major
tag-major:
	@echo "Checking git branch..."
	@if [ "$$(git rev-parse --abbrev-ref HEAD)" != "main" ]; then echo "Error: Not on main branch"; exit 1; fi
	@echo "Pulling latest changes..."
	@git pull
	@echo "Creating new major version..."
	$(eval NEW_MAJOR := $(shell expr $(MAJOR) + 1))
	$(eval NEW_VERSION := $(NEW_MAJOR).0.0)
	@echo "New version: $(NEW_VERSION)"
	@sed -i 's/"version": *"[^"]*"/"version": "v$(NEW_VERSION)"/' composer.json
	@git add composer.json
	@git commit -m "Bump version to $(NEW_VERSION)"
	@git tag -a v$(NEW_VERSION) -m "Version $(NEW_VERSION)"
	@git push && git push --tags
	@echo "Major version v$(NEW_VERSION) created and pushed to GitHub"