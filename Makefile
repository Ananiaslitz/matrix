.PHONY: up down shell test composer-install lint

## Sobe todos os containers em background
up:
	docker compose up -d --build

## Derruba os containers
down:
	docker compose down

## Abre um shell bash dentro do container app
shell:
	docker compose run --rm app bash

## Roda o composer install dentro do container
composer-install:
	docker compose run --rm app composer install --no-interaction

## Roda os testes PHPUnit dentro do container
test:
	docker compose run --rm app ./vendor/bin/phpunit --testdox --colors=always

## Roda o PHPStan (análise estática)
lint:
	docker compose run --rm app ./vendor/bin/phpstan analyse src --level=8

## Rebuild da imagem sem cache
rebuild:
	docker compose build --no-cache

## ─── Sandbox (Hyperf HTTP) ─────────────────────────────────────────────
## Sobe apenas o sandbox Hyperf (porta 9501)
sandbox-up:
	docker compose up -d sandbox

## Logs do sandbox em tempo real
sandbox-logs:
	docker compose logs -f sandbox

## Testa os endpoints do sandbox (requer curl)
test-e2e:
	@echo "→ GET /health"
	curl -s http://localhost:9501/health | jq .
	@echo "\n→ GET /tenant (via header X-Tenant-ID: acme)"
	curl -s -H "X-Tenant-ID: acme" http://localhost:9501/tenant | jq .
	@echo "\n→ GET /tenant/ping (via header X-Tenant-ID: acme)"
	curl -s -H "X-Tenant-ID: acme" http://localhost:9501/tenant/ping | jq .
