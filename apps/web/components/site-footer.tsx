import Link from "next/link";

/** Bottom of every page: the legal pages (FR-40). */
export function SiteFooter() {
  return (
    <footer className="border-t">
      <div className="mx-auto flex max-w-5xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-6 text-sm text-muted-foreground">
        <span>© 2026 CourtKu</span>
        <Link href="/terms" className="py-1 hover:text-foreground hover:underline">
          Terms of Use
        </Link>
        <Link href="/privacy" className="py-1 hover:text-foreground hover:underline">
          Privacy Notice
        </Link>
      </div>
    </footer>
  );
}
