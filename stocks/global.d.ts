export {};

declare global {
  interface Date {
    stdTimezoneOffset(): number;
    dst(): boolean;
  }
}
