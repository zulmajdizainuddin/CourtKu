import type { VenueStatus } from "@/features/venues/hooks";
import { cn } from "cn";

const statuses: Record<VenueStatus, { label: string; className: string }> = {
  pending: { label: "Waiting for approval", className: "bg-amber-500/15 text-amber-800 dark:text-amber-300" },
  active: { label: "Live", className: "bg-emerald-500/15 text-emerald-800 dark:text-emerald-300" },
  suspended: { label: "Suspended", className: "bg-destructive/10 text-destructive" },
};

/** A small coloured label: Waiting for approval, Live or Suspended. */
export function VenueStatusBadge({ status }: { status: VenueStatus }) {
  const { label, className } = statuses[status];
  return (
    <span className={cn("inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium", className)}>
      {label}
    </span>
  );
}
