DOCKER_NAME ?= autohome
VERSION ?= latest
INSTANCE ?= cli

# -----------------------------------------------------------------------------

.DEFAULT_GOAL: all
.PHONY: all clean build start

# -----------------------------------------------------------------------------

all: start
	@echo "---Complete !---"

build: clean
	@echo "---Build---"
	@docker build -t $(DOCKER_NAME):$(VERSION) .

clean:
	@echo "---Clean---"
	@docker rm -f $(DOCKER_NAME) || true
	@docker rmi $(DOCKER_NAME) || true

start:
	@echo "---Start---"
	@docker run --rm --name $(DOCKER_NAME)_$(INSTANCE) $(DOCKER_NAME):$(VERSION)
