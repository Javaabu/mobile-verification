import { Alert } from "@javaabu/forms";

export function StatusAlert({ status }: { status?: string | null }) {
  if (!status) {
    return null;
  }

  return (
    <div data-mobile-status>
      <Alert type="success">{status}</Alert>
    </div>
  );
}
