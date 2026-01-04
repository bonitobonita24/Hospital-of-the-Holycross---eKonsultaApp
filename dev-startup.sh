#!/bin/bash
# Development Environment Setup and Startup Script
# Automatically starts Docker containers and configures for development

set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo "=========================================="
echo "eKonsulta Development Environment Setup"
echo "=========================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "✗ Docker is not running. Please start Docker first."
    exit 1
fi

echo "✓ Docker is running"

# Start containers in background if not already running
if ! docker compose ps | grep -q "server.*Up"; then
    echo "Starting Docker containers..."
    docker compose up -d --build
    echo "✓ Containers started"
else
    echo "✓ Containers already running"
fi

# Wait for MySQL to be healthy
echo "Waiting for MySQL to be ready..."
for i in {1..30}; do
    if docker compose exec -T mysql mysqladmin ping -h localhost > /dev/null 2>&1; then
        echo "✓ MySQL is ready"
        break
    fi
    echo "  Waiting... ($i/30)"
    sleep 1
done

# Switch to development environment
echo "Configuring for development..."
php setenv.php dev

echo ""
echo "=========================================="
echo "✓ Development environment is ready!"
echo "=========================================="
echo ""
echo "Access the application at:"
echo "  http://localhost:8080"
echo ""
echo "MySQL Connection:"
echo "  Host: localhost"
echo "  Port: 3307"
echo "  User: root"
echo "  Password: roottoor"
echo "  Database: konsulta"
echo ""
echo "To view logs:"
echo "  docker compose logs -f server"
echo ""
echo "To stop containers:"
echo "  docker compose down"
echo ""
