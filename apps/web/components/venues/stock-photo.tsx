import Image from "next/image";
import { stockPhoto } from "@/lib/stock-photo";
import { cn } from "cn";

/**
 * A sample photo of the venue's main sport, for venues without their own photos (ADR-031). Marked
 * "Sample photo" so nobody takes it for the venue itself. Decorative: the name and sports are
 * written next to it.
 */
export function StockPhoto({
  sport,
  seed,
  width,
  height,
  priority,
  className,
}: {
  sport: string | null | undefined;
  seed: string;
  width: number;
  height: number;
  priority?: boolean;
  className?: string;
}) {
  return (
    <div className={cn("relative overflow-hidden bg-muted", className)}>
      <Image
        src={stockPhoto(sport, seed, width, height)}
        alt=""
        width={width}
        height={height}
        unoptimized // Unsplash already sends the right size and format.
        priority={priority}
        className="size-full object-cover"
      />
      <span className="absolute right-2 bottom-2 rounded bg-black/55 px-1.5 py-0.5 text-[11px] text-white">
        Sample photo
      </span>
    </div>
  );
}
