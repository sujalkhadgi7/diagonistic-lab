# Health Package Recommendation Algorithm

This document explains how package recommendations are generated on the Health Package page.

Implementation source:
- `health-package.php` (JavaScript function `getRecommendedPackages`)

## Where It Runs

The algorithm runs in the browser on the Health Package page after package data and booking history are loaded.

- Packages are read from the DOM (`.package-item` cards).
- Booking history is injected from PHP into JavaScript (`bookingHistory`).
- Recommendations are rendered into `#recommendedPackages`.

## Inputs

`getRecommendedPackages(allPackages, history, limit)`

- `allPackages`: array of package objects.
  - `id`
  - `name`
  - `description`
  - `category`
  - `pricing`
  - `popularity`
  - `relatedPackages` (array of package IDs)
- `history`: array of previously booked items.
  - `packageId`
  - `packageName`
- `limit`: max number of recommendations to return (currently `3`).

## Output

Returns an array of recommended package objects sorted by descending score and limited to `limit` items.

Each returned item has:
- Original package properties
- `score` (computed ranking score)
- Internal `sourceBookedIds` set while scoring (used to boost diversity), then returned object still carries this field from current implementation

## High-Level Strategy

1. Collect all package IDs that the user already booked.
2. Exclude already-booked packages from recommendations.
3. If user has no usable history, fallback to most popular packages.
4. Otherwise, build candidate recommendations using:
   - Related package matches (strong signal)
   - Same-category matches (secondary signal)
5. Add package popularity as a final tie-breaker/boost.
6. Sort by score descending and return top N.

## Scoring Rules

The algorithm maintains a candidate map keyed by package ID.

### Rule 1: Related package boost
For each booked package:
- For each `relatedPackages` ID:
  - If that package exists and is not already booked, add:
    - `+50` base points
    - `+15` diversity bonus if this is the first time this candidate is linked from that booked source package

### Rule 2: Category fallback boost
Only applied if candidate count is still less than `limit`.

For each booked package:
- Find unbooked packages in the same category and add:
  - `+20` base points
  - `+15` diversity bonus for first contribution from that booked source package

### Rule 3: Popularity boost
After relation/category scoring:
- Add `candidate.popularity` to final score.

This means final score is approximately:

`score = relation_points + category_points + source_diversity_bonus + popularity`

## Cold Start Behavior

If there is no booking history (or no matching booked package objects), the algorithm returns top popular packages:

1. Sort all packages by `popularity` descending.
2. Take first `limit` items.
3. Set `score = popularity`.

## Example

Assume user booked package A.

- A has related package B and C.
- B gets: `+50` (+15 first-source bonus), then `+popularity(B)`.
- C gets: `+50` (+15 first-source bonus), then `+popularity(C)`.
- If less than `limit`, same-category packages get `+20` (+15 first-source bonus) and popularity.

Final list is sorted by score and top `limit` is shown.

## Complexity

Let:
- `n` = number of all packages
- `h` = number of booked packages
- `r` = average related packages per booked package

Approximate complexity:
- Related pass: `O(h * r * n)` in current implementation because each related ID uses `find` on all packages.
- Category pass: up to `O(h * n)`.
- Sorting candidates: `O(c log c)` where `c` is candidate count.

For typical small/medium package lists this is acceptable on client side.

## Current Limitations

1. Exact name matching for booking history mapping.
   - If appointment package names differ from package table names (spacing/case/renamed records), history may not map perfectly.
2. `Array.find` used repeatedly for related lookup.
   - Could be optimized with an `id -> package` map.
3. `sourceBookedIds` remains on returned objects.
   - Not harmful, but not needed for rendering.

## Tunable Weights

You can tune behavior by changing these constants in `getRecommendedPackages`:

- Related package weight: `50`
- Same-category weight: `20`
- Unique source diversity bonus: `15`
- Result count (`limit`): currently `3`

Suggested tuning direction:
- Increase related weight for stronger personalized recommendations.
- Increase category weight when related package links are sparse.
- Lower popularity influence if you want less global bias.

## Recommended Future Improvements

1. Build `packageById` map once to avoid repeated `find` lookups.
2. Normalize name matching between appointment data and package catalog.
3. Add recency weighting (recent bookings influence more).
4. Add click-through and booking feedback loop to validate recommendation quality.
5. Hide internal scoring metadata before rendering if not needed.
