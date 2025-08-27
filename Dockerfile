# Lightweight Python base
FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1

# System deps for building common Python packages (lxml, etc.)
RUN apt-get update && apt-get install -y --no-install-recommends \
      build-essential \
      libxml2-dev libxslt1-dev \
      default-libmysqlclient-dev \
      curl ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# If you have a requirements file inside seo_script, install it
# This pattern avoids invalidating cache if only app code changes
COPY seo_script/requirements.txt /tmp/requirements.txt
RUN if [ -f /tmp/requirements.txt ]; then \
      pip install -r /tmp/requirements.txt; \
    else \
      # Fallback minimal deps for DB + tooling
      pip install "sqlalchemy>=2" "sqlmodel>=0.0.16" "pymysql>=1.1" "alembic>=1.13" "redis>=5.0"; \
    fi

# App code will be bind-mounted by docker-compose for dev
CMD ["python", "-V"]
