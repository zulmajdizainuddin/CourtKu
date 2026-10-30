# ADR-020: Price rules never overlap; a court's own rule beats an all-courts rule

- **Status:** Accepted
- **Date:** 2026-10-31

## Context

FR-10 lets owners charge peak and off-peak prices, e.g. "Mon–Fri 6:00 pm – midnight, RM 30 an hour". The plan gave each rule a `priority` number: when two rules cover the same half hour, the higher number wins.

Priority numbers are hard to explain to a venue owner. With "Weekday evenings, priority 2" and "Friday nights, priority 1", the owner has to remember which number wins, and a wrong guess quietly charges players the wrong price.

## Decision

There is no priority number. Instead:

- Two rules for the **same courts** may not cover the same half hour. The API refuses the second one and names the rule it overlaps, e.g. "This overlaps Weekday peak (Mon–Fri, 6:00 pm – midnight) for all courts."
- A rule is for **all courts** or for **one court**. They may cover the same hours, and the court's own rule wins.
- Touching is fine: 6:00 pm – 8:00 pm and 8:00 pm – midnight.

So every half hour costs, from the first that matches: that court's rule, an all-courts rule, or the court's normal price (`PricingService`).

Rules follow the business day (ADR-015): a Friday rule from 6:00 pm to 2:00 am covers Saturday 00:30 on the clock, because that is still Friday night.

## Alternatives considered

- **Priority numbers (the plan):** flexible, but the owner can't see which price applies without working it out.
- **Most recent rule wins:** no number to set, but changing an old rule would silently change which one wins.

## Consequences

- At most one rule of each kind matches a half hour, so the price is always clear and easy to test.
- Owners who want "Friday nights cost more than other weekday nights" make two rules (Mon–Thu and Fri) instead of one rule plus an exception.
- The `price_rules` table has no `priority` column.
