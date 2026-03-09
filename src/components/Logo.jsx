export function Logo({ className = '' }) {
  return (
    <img
      src="/neo-logo.svg"
      alt="Europa 4.5"
      className={className}
      width={40}
      height={40}
      loading="eager"
      decoding="async"
    />
  );
}
