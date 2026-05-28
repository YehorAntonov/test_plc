# Notes

Spend ~5 minutes here. Bullets are fine.

## Approach

- View counter: instead of storing one database row per single view, I used aggregated minute buckets. Each request increments a counter for the current minute using an upsert query. This reduces the amount of writes and avoids a naive views = views + 1 approach on every request.
- Trending query: the trending endpoint aggregates views from the last 24 hours using SUM(views_count), sorts vehicles by total views, and applies a tie-break by smaller vehicle id. I also added a short cache layer for the trending endpoint to avoid running the aggregation query on every request.
- Component: the Vue component fetches the trending endpoint on mount, displays loading and error states, and refreshes automatically every 30 seconds. Polling cleanup is handled on unmount to avoid unnecessary intervals.

## Tradeoffs / what I'd do with more time

- For a production-scale version, I would likely move the counters to Redis and periodically flush aggregated values to the database using queues or scheduled jobs.
- I kept the implementation relatively simple because the task is intentionally small.
- I would also separate some frontend fetching logic into composables if the application grew larger.
- I would also make a more beautiful UI that would be pleasant to use.

## Anything I'd flag as risky in production

- SQLite is perfectly fine for this task, but it would not be ideal for high write concurrency in production.
- The current implementation still writes directly to the database on every request, even though writes are aggregated into minute buckets.
- Cache invalidation and polling intervals would need more tuning depending on traffic and freshness requirements.
