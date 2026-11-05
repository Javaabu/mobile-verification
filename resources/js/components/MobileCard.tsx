import { Card } from "@javaabu/forms";
import type { ReactNode } from "react";

export interface MobileCardProps {
  title: string;
  page: string;
  children: ReactNode;
}

/** The card the guest pages sit in, as javaabu/auth's AuthCard */
export function MobileCard({ title, page, children }: MobileCardProps) {
  return (
    <div className="mx-auto flex w-full max-w-md flex-col gap-6 px-4 py-12" data-mobile-page={page}>
      <Card title={title}>
        <div className="flex flex-col gap-4">{children}</div>
      </Card>
    </div>
  );
}
