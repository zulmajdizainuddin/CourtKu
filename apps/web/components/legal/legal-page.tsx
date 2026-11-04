/**
 * The frame for the Terms of Use and Privacy Notice (FR-40): a readable column with simple heading
 * and list styles, so the pages themselves are just headings and paragraphs.
 */
export function LegalPage({ title, updated, children }: { title: string; updated: string; children: React.ReactNode }) {
  return (
    <article className="mx-auto grid w-full max-w-2xl gap-4 px-4 py-10 text-[15px] leading-relaxed [&_a]:underline [&_h2]:mt-4 [&_h2]:scroll-mt-20 [&_h2]:text-xl [&_h2]:font-semibold [&_li]:mt-1 [&_ul]:list-disc [&_ul]:pl-5">
      <header className="grid gap-1">
        <h1 className="text-3xl font-semibold tracking-tight">{title}</h1>
        <p className="text-sm text-muted-foreground">Last updated: {updated}</p>
      </header>
      {children}
    </article>
  );
}

/** Where people reach CourtKu about their account, their data or these pages. */
export const CONTACT_EMAIL = "courtku9@gmail.com";
