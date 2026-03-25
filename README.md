# swypply-api

Private REST API powering the Swypply platform.

## Overview

This is the backend service for Swypply. It handles authentication, user data, subscription management, and communication with the mobile client.

The API is not publicly documented. Endpoints, schema, and internal logic are proprietary.

## Infrastructure

- Hosted on Railway
- PostgreSQL database via Supabase
- All environment configuration is managed through Railway's environment variable system

## Security

The service implements layered security including rate limiting, token-based authentication with short-lived sessions, email verification on registration, encrypted password storage, and HTTP security headers on all responses. Errors are logged server-side and never surfaced to clients.

## Deployment

Automatic deployment on push to main via Railway's Git integration. No manual steps required.

---

*Source is private. This repository is not open for contributions or issue reports.*
