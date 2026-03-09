<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configura&ccedil;&otilde;es
        </h2>
    </x-slot>

    <style>
        .settings-scope {
            --settings-surface: #101010;
            --settings-border: #2b2b2b;
            --settings-input-bg: #0f0f0f;
            --settings-text: #f5f5f5;
            --settings-muted: #9ca3af;
            --settings-danger-border: #7f1d1d;
            --settings-danger-text: #fda4af;
        }

        html.theme-light .settings-scope {
            --settings-surface: #ffffff;
            --settings-border: #cfe0ff;
            --settings-input-bg: #f8fbff;
            --settings-text: #0f172a;
            --settings-muted: #475569;
            --settings-danger-border: #dc2626;
            --settings-danger-text: #b91c1c;
        }

        .settings-table-card {
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background-color: var(--settings-surface);
            overflow: hidden;
        }

        .settings-filter-card {
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background-color: var(--settings-surface);
            padding: 0.6rem;
            margin-bottom: 0.65rem;
        }

        .settings-filter-grid {
            display: grid;
            gap: 0.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 1024px) {
            .settings-filter-grid {
                grid-template-columns: minmax(220px, 2fr) minmax(140px, 1fr) minmax(170px, 1fr) auto;
                align-items: end;
            }
        }

        .settings-filter-label {
            display: block;
            margin-bottom: 0.28rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--settings-muted);
        }

        .settings-filter-input,
        .settings-filter-select {
            width: 100%;
            height: 2rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background-color: var(--settings-input-bg);
            color: var(--settings-text);
            padding: 0 0.6rem;
            font-size: 0.85rem;
        }

        .settings-filter-input::placeholder {
            color: var(--settings-muted);
        }

        .settings-filter-input:focus,
        .settings-filter-select:focus {
            outline: none;
            border-color: #0080FF;
            box-shadow: 0 0 0 1px #0080FF;
        }

        .settings-add-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            justify-content: flex-start;
        }

        .settings-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            height: 2rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            padding: 0 0.65rem;
            background-color: var(--settings-input-bg);
            color: var(--settings-text);
            font-size: 0.8rem;
            font-weight: 600;
            transition: background-color 120ms ease, color 120ms ease, border-color 120ms ease;
        }

        .settings-add-btn svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .settings-add-btn:hover {
            background-color: #0080FF;
            border-color: #0080FF;
            color: #ffffff;
        }

        .settings-add-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            background-color: var(--settings-input-bg);
            color: var(--settings-muted);
            border-color: var(--settings-border);
        }

        .settings-add-btn:disabled:hover {
            background-color: var(--settings-input-bg);
            color: var(--settings-muted);
            border-color: var(--settings-border);
        }

        .settings-add-btn--icon {
            width: 2rem;
            min-width: 2rem;
            padding: 0;
            justify-content: center;
        }

        .settings-add-btn--icon svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .settings-table-grid {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) 0fr 0fr;
            transition: grid-template-columns 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .settings-table-grid.is-expanded {
            grid-template-columns: minmax(220px, 1fr) minmax(260px, 1fr) minmax(360px, 1.2fr);
        }

        .settings-col {
            min-width: 0;
            padding: 0.45rem;
            overflow: hidden;
        }

        .settings-col + .settings-col {
            border-left: 1px solid var(--settings-border);
        }

        .settings-actions {
            display: grid;
            gap: 0.35rem;
        }

        .settings-action-btn {
            border-radius: 2px !important;
            background-color: var(--settings-input-bg);
            border-color: var(--settings-border) !important;
            color: var(--settings-text) !important;
        }

        .settings-page-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }

        .settings-page-checkbox {
            width: 0.95rem;
            height: 0.95rem;
            margin: 0;
            accent-color: #0080FF;
            cursor: pointer;
            flex: 0 0 auto;
        }

        .settings-page-label {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1 1 auto;
            min-width: 0;
            text-align: left;
        }

        .settings-page-counter {
            flex: 0 0 auto;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            padding: 0.05rem 0.3rem;
            font-size: 0.68rem;
            line-height: 1.1;
            color: var(--settings-muted);
            margin-left: 0.35rem;
        }

        .settings-page-label-btn {
            display: flex;
            align-items: center;
            width: 100%;
            background: transparent;
            border: 0;
            color: inherit;
            padding: 0;
            cursor: pointer;
            min-width: 0;
        }

        .settings-page-label-btn:focus-visible {
            outline: 1px solid #0080FF;
            outline-offset: 2px;
        }

        .settings-page-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.2rem;
            height: 1.2rem;
            border: 0;
            background: transparent;
            color: inherit;
            cursor: pointer;
            flex: 0 0 auto;
            padding: 0;
        }

        .settings-page-toggle-spacer {
            width: 1.2rem;
            height: 1.2rem;
            flex: 0 0 auto;
        }

        .settings-node-chevron {
            display: inline-flex;
            width: 0.5rem;
            height: 0.5rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(-45deg);
            transition: transform 120ms ease;
        }

        .settings-node-chevron.is-open {
            transform: rotate(45deg);
        }

        .settings-action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .settings-chevron {
            display: inline-flex;
            width: 0.6rem;
            height: 0.6rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(-45deg);
            transition: transform 120ms ease;
        }

        .settings-action-btn.is-active .settings-chevron {
            transform: rotate(45deg);
        }

        .settings-action-btn:hover,
        .settings-action-btn.is-active {
            background-color: #0080FF !important;
            color: #ffffff !important;
            border-color: #0080FF !important;
        }

        html.theme-light .settings-action-btn.is-active,
        html.theme-light .settings-action-btn.is-active * {
            color: #ffffff !important;
        }

        html.theme-light .settings-action-btn.is-active .settings-page-counter,
        html.theme-light .settings-action-btn.is-active .settings-member-level {
            border-color: rgba(255, 255, 255, 0.65) !important;
            color: #ffffff !important;
        }

        .settings-card-title {
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--settings-muted);
        }

        .settings-panel-content {
            opacity: 0;
            transform-origin: left center;
            transform: perspective(900px) rotateY(-16deg) translateX(-16px) scale(0.97);
            transition: opacity 190ms ease, transform 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .settings-table-grid.is-expanded .settings-col-2 .settings-panel-content,
        .settings-table-grid.is-expanded .settings-col-3 .settings-panel-content {
            opacity: 1;
            transform: perspective(900px) rotateY(0deg) translateX(0) scale(1);
        }

        .settings-panel-content.fan-animate {
            animation: settingsFanOpen 280ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        .settings-col-3 .settings-panel-content.fan-animate {
            animation-delay: 55ms;
        }

        @keyframes settingsFanOpen {
            from {
                opacity: 0;
                transform: perspective(900px) rotateY(-16deg) translateX(-16px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: perspective(900px) rotateY(0deg) translateX(0) scale(1);
            }
        }

        .settings-placeholder {
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--settings-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .settings-member-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.6rem;
            cursor: pointer;
            flex-wrap: wrap;
        }

        .settings-member-level {
            flex: 0 0 auto;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            padding: 0.12rem 0.35rem;
            font-size: 0.72rem;
            color: var(--settings-muted);
            white-space: normal;
            overflow-wrap: anywhere;
            max-width: 100%;
        }

        .settings-user-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
        }

        .settings-user-quick-actions {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .settings-user-icon-btn {
            width: 2rem;
            height: 2rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background: var(--settings-input-bg);
            color: var(--settings-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease, opacity 120ms ease;
        }

        .settings-user-icon-btn svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .settings-user-icon-btn:hover {
            background: #0080FF;
            border-color: #0080FF;
            color: #ffffff;
        }

        .settings-user-icon-btn.is-danger {
            border-color: var(--settings-danger-border);
            color: var(--settings-danger-text);
        }

        .settings-user-icon-btn.is-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .settings-user-icon-btn.is-warning {
            border-color: #dc2626;
            color: #fca5a5;
        }

        .settings-user-icon-btn.is-warning:hover,
        .settings-user-icon-btn.is-warning.is-active {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .settings-user-icon-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-user-name-input {
            flex: 1 1 auto;
            min-width: 0;
            height: 1.85rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background-color: var(--settings-input-bg);
            color: var(--settings-text);
            padding: 0 0.55rem;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .settings-user-name-input::placeholder {
            color: var(--settings-muted);
            font-weight: 500;
        }

        .settings-user-name-input:focus {
            outline: none;
            border-color: #0080FF;
            box-shadow: 0 0 0 1px #0080FF;
        }

        .settings-user-action--danger {
            border-color: var(--settings-danger-border) !important;
            color: var(--settings-danger-text) !important;
        }

        .settings-user-action--danger:hover {
            background-color: var(--settings-danger-border) !important;
            border-color: var(--settings-danger-border) !important;
            color: #ffffff !important;
        }

        .settings-user-team-row {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .settings-user-team-label {
            font-size: 0.78rem;
            color: var(--settings-muted);
            white-space: nowrap;
        }

        .settings-user-team-row:hover .settings-user-team-label {
            color: #ffffff !important;
        }

        .settings-user-team-row:hover .settings-user-team-select {
            border-color: rgba(255, 255, 255, 0.65) !important;
        }

        .settings-user-team-select {
            flex: 1;
            height: 2rem;
            font-size: 0.8rem;
        }

        .settings-user-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
        }

        .settings-switch {
            position: relative;
            width: 2.1rem;
            height: 1.2rem;
            display: inline-flex;
            flex: 0 0 auto;
        }

        .settings-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
            pointer-events: none;
        }

        .settings-switch-track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            border: 1px solid var(--settings-border);
            background-color: var(--settings-input-bg);
            transition: background-color 120ms ease, border-color 120ms ease;
        }

        .settings-switch-thumb {
            position: absolute;
            top: 1px;
            left: 1px;
            width: 0.95rem;
            height: 0.95rem;
            border-radius: 999px;
            background-color: var(--settings-muted);
            transition: transform 120ms ease, background-color 120ms ease;
        }

        .settings-switch input:checked + .settings-switch-track {
            background-color: #ef4444;
            border-color: #ef4444;
        }

        .settings-switch input:checked + .settings-switch-track .settings-switch-thumb {
            transform: translateX(0.9rem);
            background-color: #ffffff;
        }

        .settings-switch input:disabled + .settings-switch-track {
            opacity: 0.6;
        }

        .settings-team-rename-block {
            display: grid;
            gap: 0.3rem;
        }

        .settings-team-rename-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .settings-team-rename-input {
            flex: 1;
            height: 2rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background-color: var(--settings-input-bg);
            color: var(--settings-text);
            padding: 0 0.6rem;
            font-size: 0.85rem;
            min-width: 0;
        }

        .settings-team-rename-input:focus {
            outline: none;
            border-color: #0080FF;
            box-shadow: 0 0 0 1px #0080FF;
        }

        .settings-team-rename-save-btn {
            width: 2rem;
            height: 2rem;
            border: 1px solid #0080FF;
            border-radius: 2px;
            background-color: #0080FF;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 120ms ease, border-color 120ms ease, opacity 120ms ease;
        }

        .settings-team-rename-save-btn:hover {
            background-color: #006fdc;
            border-color: #006fdc;
        }

        .settings-team-rename-save-btn.is-danger {
            border-color: #dc2626;
            background-color: transparent;
            color: #fca5a5;
        }

        .settings-team-rename-save-btn.is-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .settings-team-rename-save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-team-rename-save-btn svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .settings-team-save-message {
            font-size: 0.74rem;
            font-weight: 600;
            min-height: 1rem;
        }

        .settings-team-save-message.is-success {
            color: #22c55e;
        }

        .settings-team-save-message.is-error {
            color: #f87171;
        }

        .settings-user-save-message {
            font-size: 0.74rem;
            font-weight: 600;
            min-height: 1rem;
        }

        .settings-user-save-message.is-success {
            color: #22c55e;
        }

        .settings-user-save-message.is-error {
            color: #f87171;
        }

        .settings-user-save-btn {
            width: 100%;
            height: 2rem;
            border: 1px solid #0080FF;
            border-radius: 2px;
            background-color: #0080FF;
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            transition: background-color 120ms ease, border-color 120ms ease;
        }

        .settings-user-save-btn:hover {
            background-color: #006fdc;
            border-color: #006fdc;
        }

        .settings-user-save-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 1px #0080FF;
        }

        .settings-user-save-btn[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-permission-save-block {
            margin-top: 0.5rem;
            display: grid;
            gap: 0.3rem;
        }

        .settings-permission-save-message {
            font-size: 0.74rem;
            font-weight: 600;
            min-height: 1rem;
        }

        .settings-permission-save-message.is-success {
            color: #22c55e;
        }

        .settings-permission-save-message.is-error {
            color: #f87171;
        }

        html.theme-light .settings-permission-save-message.is-success {
            color: #166534;
        }

        html.theme-light .settings-permission-save-message.is-error {
            color: #b91c1c;
        }

        html.theme-light .settings-team-save-message.is-success {
            color: #166534;
        }

        html.theme-light .settings-team-save-message.is-error {
            color: #b91c1c;
        }

        html.theme-light .settings-user-save-message.is-success {
            color: #166534;
        }

        html.theme-light .settings-user-save-message.is-error {
            color: #b91c1c;
        }

        .settings-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 70;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .settings-modal-card {
            width: min(100%, 28rem);
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background: var(--settings-surface);
            color: var(--settings-text);
            padding: 0.9rem;
            display: grid;
            gap: 0.7rem;
        }

        .settings-modal-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .settings-modal-text {
            font-size: 0.85rem;
            color: var(--settings-muted);
        }

        .settings-modal-field {
            display: grid;
            gap: 0.3rem;
        }

        .settings-modal-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--settings-muted);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .settings-modal-users-list {
            max-height: 11rem;
            overflow: auto;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background: var(--settings-input-bg);
            padding: 0.3rem;
            display: grid;
            gap: 0.18rem;
        }

        .settings-modal-user-option {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.83rem;
            color: var(--settings-text);
            padding: 0.25rem 0.3rem;
            border-radius: 2px;
        }

        .settings-modal-user-option:hover {
            background: rgba(0, 128, 255, 0.14);
        }

        .settings-modal-user-checkbox {
            width: 0.95rem;
            height: 0.95rem;
            margin: 0;
            accent-color: #22c55e;
        }

        .settings-modal-card--wide {
            width: min(96vw, 64rem);
        }

        .settings-modal-create-user-layout {
            display: grid;
            gap: 0.8rem;
            grid-template-columns: 1fr;
        }

        .settings-modal-create-user-layout.has-team-panel {
            grid-template-columns: minmax(300px, 1fr) minmax(300px, 1fr);
        }

        .settings-modal-create-user-panel {
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background: var(--settings-input-bg);
            padding: 0.7rem;
            display: grid;
            gap: 0.6rem;
        }

        .settings-password-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.35rem;
            align-items: center;
        }

        .settings-password-toggle {
            width: 2rem;
            height: 2rem;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            background: var(--settings-input-bg);
            color: var(--settings-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease;
        }

        .settings-password-toggle svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .settings-password-toggle:hover {
            background: #0080FF;
            border-color: #0080FF;
            color: #ffffff;
        }

        .settings-modal-checkbox-line {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            color: var(--settings-text);
        }

        .settings-modal-checkbox-line input {
            width: 0.95rem;
            height: 0.95rem;
            margin: 0;
            accent-color: #22c55e;
        }

        .settings-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.45rem;
        }

        .settings-modal-btn {
            height: 2rem;
            border-radius: 2px;
            border: 1px solid var(--settings-border);
            background: var(--settings-input-bg);
            color: var(--settings-text);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0 0.8rem;
        }

        .settings-modal-btn.is-primary {
            background: #0080FF;
            border-color: #0080FF;
            color: #ffffff;
        }

        .settings-modal-btn.is-danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .settings-modal-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-dev-note {
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed var(--settings-border);
            border-radius: 2px;
            padding: 0.7rem;
            color: var(--settings-muted);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .settings-toast-stack {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1200;
            display: grid;
            gap: 0.45rem;
            width: min(92vw, 360px);
        }

        .settings-toast {
            border-radius: 4px;
            border: 1px solid var(--settings-border);
            background: rgba(8, 10, 12, 0.96);
            color: var(--settings-text);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
            padding: 0.65rem 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .settings-toast__icon {
            flex: 0 0 auto;
            width: 1rem;
            height: 1rem;
            margin-top: 0.05rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .settings-toast__text {
            flex: 1 1 auto;
            min-width: 0;
        }

        .settings-toast__close {
            flex: 0 0 auto;
            border: 0;
            background: transparent;
            color: inherit;
            opacity: 0.7;
            padding: 0;
            width: 1rem;
            height: 1rem;
            line-height: 1rem;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .settings-toast__close:hover {
            opacity: 1;
        }

        .settings-toast--success {
            border-color: #14532d;
            background: rgba(10, 45, 28, 0.92);
        }

        .settings-toast--error {
            border-color: #7f1d1d;
            background: rgba(68, 17, 17, 0.92);
        }

        .settings-toast--info {
            border-color: #1d4ed8;
            background: rgba(13, 34, 82, 0.92);
        }

        html.theme-light .settings-toast {
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.14);
        }

        html.theme-light .settings-toast--success {
            border-color: #15803d;
            background: rgba(22, 163, 74, 0.15);
        }

        html.theme-light .settings-toast--error {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.12);
        }

        html.theme-light .settings-toast--info {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
        }

        @media (max-width: 1023px) {
            .settings-table-grid,
            .settings-table-grid.is-expanded {
                grid-template-columns: 1fr;
            }

            .settings-col + .settings-col {
                border-left: 0;
                border-top: 1px solid var(--settings-border);
            }

            .settings-panel-content {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>

    <div class="settings-scope py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mt-4" x-data="settingsPanel()" x-init="init()">
                <div class="settings-toast-stack" x-cloak>
                    <template x-for="toast in toasts" :key="toast.id">
                        <div class="settings-toast" :class="`settings-toast--${toast.type}`">
                            <span class="settings-toast__icon" x-text="toastIcon(toast.type)"></span>
                            <div class="settings-toast__text" x-text="toast.message"></div>
                            <button type="button" class="settings-toast__close" x-on:click="dismissToast(toast.id)" aria-label="Fechar">x</button>
                        </div>
                    </template>
                </div>

                <div class="settings-filter-card">
                    <div class="settings-filter-grid">
                        <div>
                            <label class="settings-filter-label" for="settings-search">Busca</label>
                            <input id="settings-search" type="text" class="settings-filter-input" placeholder="Buscar por nome, perfil ou p&aacute;gina" x-model="filterSearch">
                        </div>

                        <div>
                            <label class="settings-filter-label" for="settings-team-filter">Equipes</label>
                            <select id="settings-team-filter" class="settings-filter-select" x-model="filterTeamKey">
                                <option value="">Todas</option>
                                <option value="__no_team__">Sem equipe</option>
                                <template x-for="team in teamFilterOptions()" :key="`team-filter-${team.key}`">
                                    <option :value="team.key" x-text="team.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="settings-filter-label" for="settings-created-at">Data cria&ccedil;&atilde;o</label>
                            <input id="settings-created-at" type="date" class="settings-filter-input" x-model="filterCreatedAt">
                        </div>

                        <div class="settings-add-actions">
                            <button
                                type="button"
                                class="settings-add-btn settings-add-btn--icon"
                                x-on:click="clearFilters()"
                                title="Limpar filtros"
                                aria-label="Limpar filtros"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M7 6l1 14h8l1-14"></path>
                                    <path d="m10 11 4 4"></path>
                                    <path d="m14 11-4 4"></path>
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="settings-add-btn settings-add-btn--icon"
                                x-on:click="downloadUsersTable()"
                                :disabled="filteredUsersForExport().length === 0"
                                title="Baixar tabela de usuarios"
                                aria-label="Baixar tabela de usuarios"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 3v11"></path>
                                    <path d="m7 11 5 5 5-5"></path>
                                    <path d="M4 20h16"></path>
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="settings-add-btn"
                                :disabled="!canCreateUsers()"
                                x-on:click="openAddUserModal()"
                                :title="canCreateUsers() ? 'Adicionar usuário' : 'Sem permissao para criar usuarios'"
                                :aria-label="canCreateUsers() ? 'Adicionar usuário' : 'Sem permissao para criar usuarios'"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <path d="M20 8v6"></path>
                                    <path d="M17 11h6"></path>
                                </svg>
                                <span>Adicionar Usu&aacute;rio</span>
                            </button>

                            <button
                                type="button"
                                class="settings-add-btn"
                                :disabled="!canCreateTeams()"
                                x-on:click="openAddTeamModal()"
                                :title="canCreateTeams() ? 'Adicionar equipe' : 'Sem permissao para criar equipes'"
                                :aria-label="canCreateTeams() ? 'Adicionar equipe' : 'Sem permissao para criar equipes'"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Adicionar Equipe</span>
                            </button>

                            <button type="button" class="settings-add-btn" disabled title="Em desenvolvimento" aria-disabled="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="14" rx="2"></rect>
                                    <path d="M7 20h10"></path>
                                    <path d="M9 8h6"></path>
                                    <path d="M9 12h4"></path>
                                </svg>
                                <span>Adicionar API</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="settings-table-card">
                    <div class="settings-table-grid" :class="{ 'is-expanded': isExpanded() }">
                        <section class="settings-col settings-col-1">
                            <div class="settings-card-title">MENUS</div>
                            <div class="settings-actions">
                                <template x-for="module in modules" :key="module.key">
                                    <button
                                        type="button"
                                        class="settings-action-btn block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition"
                                        :class="{ 'is-active': activeModuleKey === module.key }"
                                        x-on:click="selectModule(module.key)"
                                    >
                                        <span class="settings-action-row">
                                            <span x-text="module.label"></span>
                                            <span class="settings-chevron" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </section>

                        <section class="settings-col settings-col-2">
                            <div class="settings-panel-content" :class="{ 'fan-animate': fanPulse }">
                                <div class="settings-card-title" x-text="activeModuleColumn2Title()"></div>

                                <template x-if="isRegistersModule()">
                                    <div class="settings-dev-note">Em desenvolvimento</div>
                                </template>

                                <template x-if="!isRegistersModule() && activeItems().length > 0">
                                    <div class="settings-actions">
                                        <template x-for="item in activeItems()" :key="item.key">
                                            <button
                                                type="button"
                                                class="settings-action-btn block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition"
                                                :class="{ 'is-active': activeItemKey === item.key }"
                                                x-on:click="selectItem(item.key)"
                                                x-text="item.label"
                                            ></button>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!isRegistersModule() && activeItems().length === 0">
                                    <div class="settings-placeholder">Sem dados</div>
                                </template>
                            </div>
                        </section>

                        <section class="settings-col settings-col-3">
                            <div class="settings-panel-content" :class="{ 'fan-animate': fanPulse }">
                                <div class="settings-card-title" x-text="activeModuleColumn3Title()"></div>

                                <template x-if="isUsersModule() && activeUser()">
                                    <div class="settings-actions">
                                        <div class="settings-action-btn settings-user-action block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <input
                                                type="text"
                                                class="settings-user-name-input"
                                                placeholder="Nome do usuario"
                                                :value="currentUserNameSelection()"
                                                :disabled="!canEditUsers()"
                                                x-on:input="setCurrentUserNameSelection($event.target.value)"
                                            >
                                            <span class="settings-member-level">Selecionado</span>
                                        </div>

                                        <div class="settings-action-btn settings-user-action block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <span>A&ccedil;&otilde;es do usu&aacute;rio</span>
                                            <div class="settings-user-quick-actions">
                                                <button
                                                    type="button"
                                                    class="settings-user-icon-btn"
                                                    :disabled="!canEditUsers()"
                                                    x-on:click="openResetPasswordModal()"
                                                    title="Resetar senha"
                                                    aria-label="Resetar senha"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                                                        <path d="M3 3v6h6"></path>
                                                        <path d="M12 8v4l2.5 2.5"></path>
                                                    </svg>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="settings-user-icon-btn is-warning"
                                                    :class="{ 'is-active': isCurrentUserInactive() }"
                                                    :disabled="!canEditUsers() || savingUserStatus || isCurrentUserSelf()"
                                                    x-on:click="toggleCurrentUserInactive(!isCurrentUserInactive())"
                                                    :title="isCurrentUserSelf() ? 'Nao permitido para o proprio usuario' : (isCurrentUserInactive() ? 'Ativar usuário' : 'Inativar usuário')"
                                                    :aria-label="isCurrentUserSelf() ? 'Nao permitido para o proprio usuario' : (isCurrentUserInactive() ? 'Ativar usuário' : 'Inativar usuário')"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M12 2v10"></path>
                                                        <path d="M18.36 6.64a9 9 0 1 1-12.72 0"></path>
                                                    </svg>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="settings-user-icon-btn is-danger"
                                                    :disabled="!canDeleteUsers() || isCurrentUserSelf()"
                                                    x-on:click="openDeleteUserModal()"
                                                    :title="isCurrentUserSelf() ? 'Nao permitido para o proprio usuario' : 'Excluir usuário'"
                                                    :aria-label="isCurrentUserSelf() ? 'Nao permitido para o proprio usuario' : 'Excluir usuário'"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M3 6h18"></path>
                                                        <path d="M8 6V4h8v2"></path>
                                                        <path d="M7 6l1 14h8l1-14"></path>
                                                        <path d="M10 11v6"></path>
                                                        <path d="M14 11v6"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="settings-action-btn settings-user-team-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <span class="settings-user-team-label">Alterar equipe</span>
                                            <select
                                                class="settings-filter-select settings-user-team-select"
                                                :value="currentUserTeamSelection()"
                                                :disabled="!canEditUsers()"
                                                x-on:change="setCurrentUserTeamSelection($event.target.value)"
                                            >
                                                <option value="">Sem equipe</option>
                                                <template x-for="team in teamOptions" :key="team.key">
                                                    <option :value="team.key" x-text="team.label"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="settings-action-btn settings-user-team-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <span class="settings-user-team-label">Alterar hierarquia</span>
                                            <select
                                                class="settings-filter-select settings-user-team-select"
                                                :value="currentUserRoleSelection()"
                                                :disabled="!canEditUsers()"
                                                x-on:change="setCurrentUserRoleSelection($event.target.value)"
                                            >
                                                <option value="">Sem hierarquia</option>
                                                <template x-for="role in hierarchyOptions" :key="role.key">
                                                    <option :value="role.key" x-text="role.displayLabel"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <button
                                            type="button"
                                            class="settings-user-save-btn"
                                            :disabled="savingUserTeam || !canEditUsers()"
                                            x-on:click="saveCurrentUserTeam()"
                                            x-text="savingUserTeam ? 'Salvando...' : 'Salvar'"
                                        ></button>
                                        <div
                                            class="settings-user-save-message"
                                            :class="{ 'is-success': userTeamSaveStatus === 'success', 'is-error': userTeamSaveStatus === 'error' }"
                                            x-text="userTeamSaveMessage"
                                        ></div>
                                    </div>
                                </template>

                                <template x-if="isUsersModule() && !activeUser()">
                                    <div class="settings-placeholder">Selecione um usuario</div>
                                </template>

                                <template x-if="isRegistersModule()">
                                    <div class="settings-dev-note">Em desenvolvimento</div>
                                </template>

                                <template x-if="isTeamsModule() && activeTeam()">
                                    <div class="settings-actions">
                                        <div class="settings-team-rename-block">
                                            <div class="settings-team-rename-row">
                                                <input
                                                    type="text"
                                                    class="settings-team-rename-input"
                                                    x-model="activeTeamNameDraft"
                                                    placeholder="Nome da equipe"
                                                    :disabled="!canEditTeams()"
                                                >
                                                <button
                                                    type="button"
                                                    class="settings-team-rename-save-btn"
                                                    :disabled="savingTeamName || !canRenameActiveTeam() || !canEditTeams()"
                                                    x-on:click="saveActiveTeamName()"
                                                    title="Salvar nome da equipe"
                                                    aria-label="Salvar nome da equipe"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                                        <path d="M17 21v-8H7v8"></path>
                                                        <path d="M7 3v5h8"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="settings-team-rename-save-btn is-danger"
                                                    :disabled="deletingTeam || !canDeleteActiveTeam() || !canEditTeams()"
                                                    x-on:click="openDeleteTeamModal()"
                                                    :title="isOwnActiveTeam() ? 'Nao e permitido excluir a propria equipe' : 'Excluir equipe'"
                                                    :aria-label="isOwnActiveTeam() ? 'Nao e permitido excluir a propria equipe' : 'Excluir equipe'"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M3 6h18"></path>
                                                        <path d="M8 6V4h8v2"></path>
                                                        <path d="M7 6l1 14h8l1-14"></path>
                                                        <path d="M10 11v6"></path>
                                                        <path d="M14 11v6"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div
                                                class="settings-team-save-message"
                                                :class="{ 'is-success': teamSaveStatus === 'success', 'is-error': teamSaveStatus === 'error' }"
                                                x-text="teamSaveMessage"
                                            ></div>
                                        </div>

                                        <template x-if="activeTeamMembers().length > 0">
                                            <div class="settings-actions">
                                                <template x-for="member in activeTeamMembers()" :key="member.key">
                                                    <button
                                                        type="button"
                                                        class="settings-action-btn settings-member-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition"
                                                        x-on:click="openUserFromTeam(member.userKey)"
                                                    >
                                                        <span class="settings-page-label" x-text="member.label"></span>
                                                        <span class="settings-member-level" x-text="member.permissionLevel"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="activeTeamMembers().length === 0">
                                            <div class="settings-placeholder">Sem usuarios nesta equipe</div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="isTeamsModule() && !activeTeam()">
                                    <div class="settings-placeholder">Selecione uma equipe</div>
                                </template>

                                <template x-if="!isUsersModule() && !isTeamsModule() && !isRegistersModule() && activePageRows().length > 0">
                                    <div class="settings-actions">
                                        <template x-for="page in activePageRows()" :key="page.key">
                                            <div class="settings-action-btn settings-page-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                                <input
                                                    type="checkbox"
                                                    class="settings-page-checkbox"
                                                    :checked="isPageEnabled(page.key)"
                                                    :disabled="(isPermissionsModule() && isMasterRoleSelected()) || !canEditConfig()"
                                                    x-on:change="togglePage(page.key, $event.target.checked)"
                                                >
                                                <button
                                                    type="button"
                                                    class="settings-page-label-btn"
                                                    x-on:click="page.hasChildren && togglePageNode(page.key)"
                                                    :disabled="!page.hasChildren"
                                                >
                                                    <span
                                                        class="settings-page-label"
                                                        :style="`padding-left:${page.depth * 14}px`"
                                                        x-text="page.label"
                                                    ></span>
                                                    <template x-if="page.hasChildren && pageCounterLabel(page.key) !== ''">
                                                        <span class="settings-page-counter" x-text="pageCounterLabel(page.key)"></span>
                                                    </template>
                                                </button>

                                                <template x-if="page.hasChildren">
                                                    <button type="button" class="settings-page-toggle" x-on:click="togglePageNode(page.key)">
                                                        <span class="settings-node-chevron" :class="{ 'is-open': isPageNodeExpanded(page.key) }"></span>
                                                    </button>
                                                </template>

                                                <template x-if="!page.hasChildren">
                                                    <span class="settings-page-toggle-spacer"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!isUsersModule() && !isTeamsModule() && !isRegistersModule() && activePageRows().length === 0">
                                    <div class="settings-placeholder">Sem dados</div>
                                </template>

                                <template x-if="isPermissionsModule() && activePermissionRole()">
                                    <div class="settings-permission-save-block">
                                        <button
                                            type="button"
                                            class="settings-user-save-btn"
                                            :disabled="savingPermissions || isMasterRoleSelected() || !canEditConfig()"
                                            x-on:click="saveRolePermissions()"
                                            x-text="savingPermissions ? 'Salvando...' : (!canEditConfig() ? 'Sem permissao para editar' : (isMasterRoleSelected() ? 'Master sempre tem acesso total' : 'Salvar permissoes'))"
                                        ></button>
                                        <div
                                            class="settings-permission-save-message"
                                            :class="{ 'is-success': permissionSaveStatus === 'success', 'is-error': permissionSaveStatus === 'error' }"
                                            x-text="permissionSaveMessage"
                                        ></div>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>
                </div>

    <div
        x-show="showAddUserModal"
        x-transition.opacity
        class="settings-modal-backdrop"
        style="display: none;"
        x-on:keydown.escape.window="closeAddUserModal()"
    >
        <div class="settings-modal-card settings-modal-card--wide" x-on:click.stop>
            <div class="settings-modal-title">Adicionar usuario</div>

            <div class="settings-modal-create-user-layout" :class="{ 'has-team-panel': addUserCreateTeam }">
                <div class="settings-modal-create-user-panel">
                    <div class="settings-modal-field">
                        <label class="settings-modal-label" for="new-user-name">Nome</label>
                        <input
                            id="new-user-name"
                            type="text"
                            class="settings-filter-input"
                            placeholder="Nome do usuario"
                            :value="newUserName"
                            x-on:input="setNewUserName($event.target.value)"
                        >
                    </div>

                    <div class="settings-modal-field">
                        <label class="settings-modal-label" for="new-user-password">Senha</label>
                        <div class="settings-password-row">
                            <input
                                id="new-user-password"
                                :type="showNewUserPassword ? 'text' : 'password'"
                                class="settings-filter-input"
                                placeholder="Digite a senha"
                                x-model="newUserPassword"
                            >
                            <button
                                type="button"
                                class="settings-password-toggle"
                                x-on:click="toggleNewUserPasswordVisibility()"
                                :title="showNewUserPassword ? 'Ocultar senha' : 'Mostrar senha'"
                                :aria-label="showNewUserPassword ? 'Ocultar senha' : 'Mostrar senha'"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="settings-modal-field">
                        <label class="settings-modal-label" for="new-user-role">Hierarquia</label>
                        <select id="new-user-role" class="settings-filter-select" x-model="newUserRoleKey">
                            <option value="">Selecione</option>
                            <template x-for="role in hierarchyOptions" :key="`new-user-role-${role.key}`">
                                <option :value="role.key" x-text="role.displayLabel"></option>
                            </template>
                        </select>
                    </div>

                    <div class="settings-modal-field">
                        <label class="settings-modal-label" for="new-user-team">Equipe (opcional)</label>
                        <select id="new-user-team" class="settings-filter-select" x-model="newUserTeamKey" :disabled="addUserCreateTeam">
                            <option value="">Sem equipe</option>
                            <template x-for="team in teamOptions" :key="`new-user-team-${team.key}`">
                                <option :value="team.key" x-text="team.label"></option>
                            </template>
                        </select>
                    </div>

                    <label class="settings-modal-checkbox-line">
                        <input type="checkbox" :checked="addUserCreateTeam" x-on:change="setAddUserCreateTeam($event.target.checked)">
                        <span>Adicionar equipe nova</span>
                    </label>
                </div>

                <template x-if="addUserCreateTeam">
                    <div class="settings-modal-create-user-panel">
                        <div class="settings-modal-title">Nova equipe</div>

                        <div class="settings-modal-field">
                            <label class="settings-modal-label" for="new-user-team-name">Nome da equipe</label>
                            <input
                                id="new-user-team-name"
                                type="text"
                                class="settings-filter-input"
                                placeholder="Ex: Operacional"
                                :value="addUserTeamName"
                                x-on:input="setAddUserTeamName($event.target.value)"
                            >
                        </div>

                        <div class="settings-modal-field">
                            <label class="settings-modal-label" for="new-user-team-supervisor">Supervisor da equipe</label>
                            <select
                                id="new-user-team-supervisor"
                                class="settings-filter-select"
                                :value="addUserTeamSupervisorKey"
                                x-on:change="setAddUserTeamSupervisor($event.target.value)"
                            >
                                <option value="">Sem supervisor</option>
                                <template x-for="user in teamCreateUsers()" :key="`new-user-team-supervisor-${user.key}`">
                                    <option :value="user.key" x-text="user.label"></option>
                                </template>
                            </select>
                        </div>

                        <div class="settings-modal-field">
                            <label class="settings-modal-label">Usuarios da equipe</label>
                            <div class="settings-modal-users-list">
                                <template x-if="teamCreateUsers().length === 0">
                                    <div class="settings-modal-text">Sem usuarios disponiveis.</div>
                                </template>
                                <template x-for="user in teamCreateUsers()" :key="`new-user-team-member-${user.key}`">
                                    <label class="settings-modal-user-option">
                                        <input
                                            type="checkbox"
                                            class="settings-modal-user-checkbox"
                                            :checked="isAddUserTeamUserSelected(user.key)"
                                            x-on:change="toggleAddUserTeamUser(user.key, $event.target.checked)"
                                        >
                                        <span x-text="user.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="settings-modal-actions">
                <button type="button" class="settings-modal-btn" x-on:click="closeAddUserModal()">Cancelar</button>
                <button
                    type="button"
                    class="settings-modal-btn is-primary"
                    :disabled="creatingUser"
                    x-on:click="submitCreateUser()"
                    x-text="creatingUser ? 'Salvando...' : 'Criar usuario'"
                ></button>
            </div>
        </div>
    </div>

    <div
        x-show="showAddTeamModal"
        x-transition.opacity
        class="settings-modal-backdrop"
        style="display: none;"
        x-on:keydown.escape.window="closeAddTeamModal()"
    >
        <div class="settings-modal-card" x-on:click.stop>
            <div class="settings-modal-title">Adicionar equipe</div>

            <div class="settings-modal-field">
                <label class="settings-modal-label" for="new-team-name">Nome da equipe</label>
                <input
                    id="new-team-name"
                    type="text"
                    class="settings-filter-input"
                    placeholder="Ex: Comercial"
                    :value="newTeamName"
                    x-on:input="setNewTeamName($event.target.value)"
                    x-on:keydown.enter.prevent="submitCreateTeam()"
                >
                <div class="settings-modal-text">A primeira letra sera maiuscula automaticamente.</div>
            </div>

            <div class="settings-modal-field">
                <label class="settings-modal-label" for="new-team-supervisor">Supervisor da equipe</label>
                <select
                    id="new-team-supervisor"
                    class="settings-filter-select"
                    :value="newTeamSupervisorKey"
                    x-on:change="setAddTeamSupervisor($event.target.value)"
                >
                    <option value="">Sem supervisor</option>
                    <template x-for="user in teamCreateUsers()" :key="`supervisor-${user.key}`">
                        <option :value="user.key" x-text="user.label"></option>
                    </template>
                </select>
            </div>

            <div class="settings-modal-field">
                <label class="settings-modal-label">Usuarios da equipe</label>
                <div class="settings-modal-users-list">
                    <template x-if="teamCreateUsers().length === 0">
                        <div class="settings-modal-text">Sem usuarios disponiveis.</div>
                    </template>
                    <template x-for="user in teamCreateUsers()" :key="`member-${user.key}`">
                        <label class="settings-modal-user-option">
                            <input
                                type="checkbox"
                                class="settings-modal-user-checkbox"
                                :checked="isTeamCreateUserSelected(user.key)"
                                x-on:change="toggleTeamCreateUser(user.key, $event.target.checked)"
                            >
                            <span x-text="user.label"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="settings-modal-actions">
                <button type="button" class="settings-modal-btn" x-on:click="closeAddTeamModal()">Cancelar</button>
                <button
                    type="button"
                    class="settings-modal-btn is-primary"
                    :disabled="creatingTeam"
                    x-on:click="submitCreateTeam()"
                    x-text="creatingTeam ? 'Salvando...' : 'Criar equipe'"
                ></button>
            </div>
        </div>
    </div>

    <div
        x-show="showResetPasswordModal"
        x-transition.opacity
        class="settings-modal-backdrop"
        style="display: none;"
        x-on:keydown.escape.window="closeResetPasswordModal()"
    >
        <div class="settings-modal-card" x-on:click.stop>
            <div class="settings-modal-title">Resetar senha</div>
            <div class="settings-modal-text">
                Informe a nova senha para <strong x-text="activeUser()?.label ?? 'usu&aacute;rio selecionado'"></strong>.
            </div>
            <input
                type="password"
                class="settings-filter-input"
                placeholder="Nova senha"
                x-model="resetPasswordValue"
                x-on:keydown.enter.prevent="submitResetPassword()"
            >
            <div class="settings-modal-actions">
                <button type="button" class="settings-modal-btn" x-on:click="closeResetPasswordModal()">Cancelar</button>
                <button
                    type="button"
                    class="settings-modal-btn is-primary"
                    :disabled="resettingPassword"
                    x-on:click="submitResetPassword()"
                    x-text="resettingPassword ? 'Salvando...' : 'Confirmar'"
                ></button>
            </div>
        </div>
    </div>

    <div
        x-show="showDeleteUserModal"
        x-transition.opacity
        class="settings-modal-backdrop"
        style="display: none;"
        x-on:keydown.escape.window="closeDeleteUserModal()"
    >
        <div class="settings-modal-card" x-on:click.stop>
            <div class="settings-modal-title">Confirmar exclus&atilde;o</div>
            <div class="settings-modal-text">
                Deseja realmente excluir <strong x-text="activeUser()?.label ?? 'usu&aacute;rio selecionado'"></strong>?
            </div>
            <div class="settings-modal-actions">
                <button type="button" class="settings-modal-btn" x-on:click="closeDeleteUserModal()">Cancelar</button>
                <button
                    type="button"
                    class="settings-modal-btn is-danger"
                    :disabled="deletingUser"
                    x-on:click="confirmDeleteUser()"
                    x-text="deletingUser ? 'Excluindo...' : 'Excluir'"
                ></button>
            </div>
        </div>
    </div>

    <div
        x-show="showDeleteTeamModal"
        x-transition.opacity
        class="settings-modal-backdrop"
        style="display: none;"
        x-on:keydown.escape.window="closeDeleteTeamModal()"
    >
        <div class="settings-modal-card" x-on:click.stop>
            <div class="settings-modal-title">Confirmar exclus&atilde;o da equipe</div>
            <div class="settings-modal-text">
                Deseja realmente excluir a equipe <strong x-text="activeTeam()?.label ?? 'selecionada'"></strong>?
            </div>
            <div class="settings-modal-text">
                Os usu&aacute;rios desta equipe ficar&atilde;o sem equipe.
            </div>
            <div class="settings-modal-actions">
                <button type="button" class="settings-modal-btn" x-on:click="closeDeleteTeamModal()">Cancelar</button>
                <button
                    type="button"
                    class="settings-modal-btn is-danger"
                    :disabled="deletingTeam"
                    x-on:click="confirmDeleteTeam()"
                    x-text="deletingTeam ? 'Excluindo...' : 'Excluir equipe'"
                ></button>
            </div>
        </div>
    </div>

            </div>
        </div>
    </div>

    <script>
        function settingsPanel() {
            const usersFromDb = @js($dbUsers ?? []);
            const teamsFromDb = @js($dbTeams ?? []);
            const teamMembersByTeam = @js($teamMembersByTeam ?? []);
            const permissionRoles = @js($permissionRoles ?? []);
            const permissionsTree = @js($permissionsTree ?? []);
            const permissionsStateByRole = @js($permissionsStateByRole ?? []);
            const permissionsSaveUrl = @js($permissionsSaveUrl ?? '');
            const teamsRenameUrl = @js($teamsRenameUrl ?? '');
            const teamsDeleteUrl = @js($teamsDeleteUrl ?? '');
            const teamsCreateUrl = @js($teamsCreateUrl ?? '');
            const usersCreateUrl = @js($usersCreateUrl ?? '');
            const usersSaveTeamUrl = @js($usersSaveTeamUrl ?? '');
            const usersStatusSaveUrl = @js($usersStatusSaveUrl ?? '');
            const usersResetPasswordUrl = @js($usersResetPasswordUrl ?? '');
            const usersDeleteUrl = @js($usersDeleteUrl ?? '');
            const authUserId = @js($authUserId ?? null);
            const authUserTeamId = @js($authUserTeamId ?? null);
            const authRoleSlug = @js($authRoleSlug ?? '');
            const authScopeMode = @js($authScopeMode ?? 'self');
            const authAllowedPermissionSlugs = @js($authAllowedPermissionSlugs ?? []);

            return {
                modules: [
                    {
                        key: 'permissions',
                        label: 'Permiss\u00f5es',
                        column2Title: 'Perfis',
                        column3Title: 'P\u00e1ginas e permiss\u00f5es',
                        items: permissionRoles.length > 0
                            ? permissionRoles
                            : [
                                { key: 'role-master', label: 'Master', role_id: null, slug: 'master' }
                            ]
                    },
                    {
                        key: 'users',
                        label: 'Usu\u00e1rios',
                        column2Title: 'Usu\u00e1rios',
                        column3Title: 'Configuracoes do usuario',
                        items: usersFromDb
                    },
                    {
                        key: 'teams',
                        label: 'Equipes',
                        column2Title: 'Equipes',
                        column3Title: 'Usuarios e nivel de permissao',
                        items: teamsFromDb.length > 0
                            ? teamsFromDb.map((team) => ({
                                key: team.key,
                                label: team.label,
                                team_id: team.team_id ?? null,
                            }))
                            : [
                                { key: 'team-comercial', label: 'Comercial', team_id: null },
                                { key: 'team-operacional', label: 'Operacional', team_id: null }
                            ]
                    },
                    {
                        key: 'registers',
                        label: 'Cadastro API',
                        column2Title: 'Cadastro API',
                        column3Title: 'P\u00e1ginas e permiss\u00f5es',
                        items: [
                            { key: 'clientes', label: 'Clientes' },
                            { key: 'produtos', label: 'Produtos' }
                        ]
                    }
                ],
                teamOptions: (() => {
                    const baseTeams = Array.isArray(teamsFromDb) ? teamsFromDb : [];
                    const options = baseTeams.map((team) => ({
                        key: team.key,
                        label: team.label,
                        team_id: team.team_id ?? null,
                    }));
                    const optionKeys = new Set(options.map((team) => team.key));

                    usersFromDb.forEach((user) => {
                        const userTeamId = user?.team_id ?? null;
                        if (userTeamId === null || userTeamId === undefined) {
                            return;
                        }

                        const fallbackKey = `team-${userTeamId}`;
                        if (optionKeys.has(fallbackKey)) {
                            return;
                        }

                        options.push({
                            key: fallbackKey,
                            label: (user?.team_label ?? '').toString().trim() || `Equipe #${userTeamId}`,
                            team_id: Number(userTeamId),
                        });
                        optionKeys.add(fallbackKey);
                    });

                    return options;
                })(),
                hierarchyOptions: (() => {
                    const baseRoles = Array.isArray(permissionRoles) ? permissionRoles : [];
                    const options = baseRoles.map((role) => ({
                        key: role.key,
                        label: role.label,
                        role_id: role.role_id ?? null,
                        nivel: role.nivel ?? null,
                        displayLabel: role.nivel !== null && role.nivel !== undefined
                            ? `${role.label} (Nivel ${role.nivel})`
                            : role.label,
                    }));
                    const optionKeys = new Set(options.map((role) => role.key));

                    usersFromDb.forEach((user) => {
                        const userRoleId = user?.role_id ?? null;
                        if (userRoleId === null || userRoleId === undefined) {
                            return;
                        }

                        const fallbackKey = `role-${userRoleId}`;
                        if (optionKeys.has(fallbackKey)) {
                            return;
                        }

                        const roleLabel = (user?.role_label ?? '').toString().trim() || `Role #${userRoleId}`;
                        const roleLevel = user?.role_nivel ?? null;
                        options.push({
                            key: fallbackKey,
                            label: roleLabel,
                            role_id: Number(userRoleId),
                            nivel: roleLevel,
                            displayLabel: roleLevel !== null && roleLevel !== undefined
                                ? `${roleLabel} (Nivel ${roleLevel})`
                                : roleLabel,
                        });
                        optionKeys.add(fallbackKey);
                    });

                    return options;
                })(),
                pagesCatalog: permissionsTree.length > 0
                    ? permissionsTree
                    : [
                        { key: 'dashboard', label: 'Painel' }
                    ],
                teamMembersByTeam: teamMembersByTeam,
                enabledPagesBySelection: permissionsStateByRole && typeof permissionsStateByRole === 'object'
                    ? permissionsStateByRole
                    : {},
                expandedPageNodes: {},
                usersTeamSelection: {},
                usersNameSelection: {},
                usersRoleSelection: {},
                filterSearch: '',
                filterTeamKey: '',
                filterCreatedAt: '',
                showAddTeamModal: false,
                creatingTeam: false,
                newTeamName: '',
                newTeamSupervisorKey: '',
                newTeamUserKeys: [],
                showAddUserModal: false,
                creatingUser: false,
                newUserName: '',
                newUserPassword: '',
                showNewUserPassword: false,
                newUserRoleKey: '',
                newUserTeamKey: '',
                addUserCreateTeam: false,
                addUserTeamName: '',
                addUserTeamSupervisorKey: '',
                addUserTeamUserKeys: [],
                activeModuleKey: null,
                activeItemKey: null,
                fanPulse: false,
                permissionsSaveUrl: permissionsSaveUrl,
                teamsRenameUrl: teamsRenameUrl,
                teamsDeleteUrl: teamsDeleteUrl,
                teamsCreateUrl: teamsCreateUrl,
                usersCreateUrl: usersCreateUrl,
                usersSaveTeamUrl: usersSaveTeamUrl,
                usersStatusSaveUrl: usersStatusSaveUrl,
                usersResetPasswordUrl: usersResetPasswordUrl,
                usersDeleteUrl: usersDeleteUrl,
                authUserId: authUserId,
                authUserTeamId: authUserTeamId,
                authRoleSlug: (authRoleSlug ?? '').toString().toLowerCase(),
                authScopeMode: (authScopeMode ?? 'self').toString().toLowerCase(),
                authAllowedPermissionSlugs: Array.isArray(authAllowedPermissionSlugs)
                    ? authAllowedPermissionSlugs.map((slug) => (slug ?? '').toString().toLowerCase())
                    : [],
                permissionSaveStatus: '',
                permissionSaveMessage: '',
                savingPermissions: false,
                teamSaveStatus: '',
                teamSaveMessage: '',
                savingTeamName: false,
                deletingTeam: false,
                activeTeamNameDraft: '',
                userTeamSaveStatus: '',
                userTeamSaveMessage: '',
                savingUserTeam: false,
                savingUserStatus: false,
                showResetPasswordModal: false,
                resetPasswordValue: '',
                resettingPassword: false,
                showDeleteUserModal: false,
                showDeleteTeamModal: false,
                deletingUser: false,
                toasts: [],
                nextToastId: 1,
                toastTimers: {},
                pendingToastStorageKey: 'settings.pending-toast',

                init() {
                    this.modules = this.modules.filter((module) => this.canAccessModule(module.key));
                    const firstModule = this.modules.length > 0 ? this.modules[0] : null;
                    this.activeModuleKey = firstModule?.key ?? null;
                    this.activeItemKey = null;
                    this.collapseAllPageNodes();
                    this.ensureSelectedItem();
                    this.consumePendingToast();
                },

                isMasterRole() {
                    return this.authRoleSlug === 'master';
                },

                hasPermissionSlug(permissionSlug) {
                    const slug = (permissionSlug ?? '').toString().toLowerCase().trim();
                    if (slug === '') {
                        return false;
                    }

                    if (this.isMasterRole()) {
                        return true;
                    }

                    return this.authAllowedPermissionSlugs.includes(slug);
                },

                hasAnyPermissionSlug(permissionSlugs) {
                    if (!Array.isArray(permissionSlugs) || permissionSlugs.length === 0) {
                        return false;
                    }

                    return permissionSlugs.some((slug) => this.hasPermissionSlug(slug));
                },

                canAccessModule(moduleKey) {
                    if (this.isMasterRole()) {
                        return true;
                    }

                    return ({
                        permissions: this.hasPermissionSlug('config.view'),
                        users: this.hasPermissionSlug('users.view'),
                        teams: this.hasPermissionSlug('equipes.view'),
                        registers: this.hasAnyPermissionSlug(['consulta.v8.view', 'consulta.presenca.view']),
                    }[moduleKey]) ?? true;
                },

                canCreateUsers() {
                    return this.hasPermissionSlug('users.create');
                },

                canCreateTeams() {
                    return this.authScopeMode === 'all';
                },

                canEditUsers() {
                    return this.hasPermissionSlug('users.edit');
                },

                canDeleteUsers() {
                    return this.hasPermissionSlug('users.delete');
                },

                canEditTeams() {
                    return this.hasPermissionSlug('equipes.edit');
                },

                canEditConfig() {
                    return this.hasPermissionSlug('config.edit');
                },

                toastIcon(type) {
                    if (type === 'success') {
                        return '+';
                    }

                    if (type === 'error') {
                        return '!';
                    }

                    return 'i';
                },

                pushToast(type, message, duration = 4200) {
                    if (!message) {
                        return;
                    }

                    const id = this.nextToastId++;
                    this.toasts.push({
                        id,
                        type: ['success', 'error', 'info'].includes(type) ? type : 'info',
                        message,
                    });

                    if (duration > 0) {
                        const timer = setTimeout(() => {
                            this.dismissToast(id);
                        }, duration);
                        this.toastTimers[id] = timer;
                    }
                },

                dismissToast(id) {
                    this.toasts = this.toasts.filter((toast) => toast.id !== id);

                    if (this.toastTimers[id]) {
                        clearTimeout(this.toastTimers[id]);
                        delete this.toastTimers[id];
                    }
                },

                persistToastForReload(type, message) {
                    if (!message) {
                        return;
                    }

                    try {
                        sessionStorage.setItem(this.pendingToastStorageKey, JSON.stringify({
                            type,
                            message,
                        }));
                    } catch (error) {
                        // Silently ignore storage failures.
                    }
                },

                consumePendingToast() {
                    try {
                        const rawToast = sessionStorage.getItem(this.pendingToastStorageKey);
                        if (!rawToast) {
                            return;
                        }

                        sessionStorage.removeItem(this.pendingToastStorageKey);
                        const payload = JSON.parse(rawToast);
                        if (!payload?.message) {
                            return;
                        }

                        this.pushToast(payload.type || 'info', payload.message, 5200);
                    } catch (error) {
                        // Ignore malformed data.
                    }
                },

                clearFilters() {
                    this.filterSearch = '';
                    this.filterTeamKey = '';
                    this.filterCreatedAt = '';
                },

                teamFilterOptions() {
                    const options = Array.isArray(this.teamOptions) ? this.teamOptions : [];

                    return options
                        .filter((team) => team && (team.key ?? '') !== '')
                        .slice()
                        .sort((a, b) => (a?.label ?? '').toString().localeCompare((b?.label ?? '').toString(), 'pt-BR', { sensitivity: 'base' }));
                },

                normalizedFilterSearch() {
                    return (this.filterSearch ?? '').toString().trim().toLowerCase();
                },

                matchesSearchTerm(item, searchTerm) {
                    if (searchTerm === '') {
                        return true;
                    }

                    const haystack = [
                        item?.label,
                        item?.name,
                        item?.login,
                        item?.team_label,
                        item?.role_label,
                    ]
                        .map((value) => (value ?? '').toString().toLowerCase());

                    return haystack.some((text) => text.includes(searchTerm));
                },

                matchesCreatedAtFilter(item) {
                    const filterDate = (this.filterCreatedAt ?? '').toString().trim();
                    if (filterDate === '') {
                        return true;
                    }

                    const createdAtIso = (item?.created_at_iso ?? '').toString().trim();
                    return createdAtIso !== '' && createdAtIso === filterDate;
                },

                matchesTeamFilter(item) {
                    const teamFilterKey = (this.filterTeamKey ?? '').toString().trim();
                    if (teamFilterKey === '') {
                        return true;
                    }

                    if (teamFilterKey === '__no_team__') {
                        return ((item?.team_key ?? '').toString().trim() === '')
                            && (item?.team_id === null || item?.team_id === undefined);
                    }

                    return (item?.team_key ?? '').toString().trim() === teamFilterKey;
                },

                userMatchesFilters(item, searchTerm) {
                    return this.matchesSearchTerm(item, searchTerm)
                        && this.matchesTeamFilter(item)
                        && this.matchesCreatedAtFilter(item);
                },

                teamMatchesFilters(item, searchTerm) {
                    if (!this.matchesSearchTerm(item, searchTerm)) {
                        return false;
                    }

                    const teamFilterKey = (this.filterTeamKey ?? '').toString().trim();
                    if (teamFilterKey === '') {
                        return true;
                    }

                    if (teamFilterKey === '__no_team__') {
                        return false;
                    }

                    return (item?.key ?? '').toString().trim() === teamFilterKey;
                },

                filteredUsersForExport() {
                    const usersModule = this.usersModule();
                    const usersItems = Array.isArray(usersModule?.items) ? usersModule.items : [];
                    const searchTerm = this.normalizedFilterSearch();

                    return usersItems.filter((item) => this.userMatchesFilters(item, searchTerm));
                },

                formatUserCreatedAt(user) {
                    const label = (user?.created_at_label ?? '').toString().trim();
                    if (label !== '') {
                        return label;
                    }

                    const iso = (user?.created_at_iso ?? '').toString().trim();
                    if (iso === '') {
                        return '';
                    }

                    const parts = iso.split('-');
                    if (parts.length === 3) {
                        return `${parts[2]}/${parts[1]}/${parts[0]}`;
                    }

                    return iso;
                },

                csvEscape(value) {
                    const text = (value ?? '').toString();
                    return `"${text.replace(/"/g, '""')}"`;
                },

                downloadUsersTable() {
                    const rows = this.filteredUsersForExport();
                    if (rows.length === 0) {
                        this.pushToast('error', 'Nenhum usuario disponivel para exportar com os filtros atuais.');
                        return;
                    }

                    const lines = ['nome;data de cadastro;equipe'];
                    rows.forEach((user) => {
                        const nome = (user?.name ?? user?.label ?? '').toString().trim();
                        const dataCadastro = this.formatUserCreatedAt(user);
                        const equipe = (user?.team_label ?? '').toString().trim() || 'Sem equipe';
                        lines.push([
                            this.csvEscape(nome),
                            this.csvEscape(dataCadastro),
                            this.csvEscape(equipe),
                        ].join(';'));
                    });

                    const csvContent = `\uFEFF${lines.join('\r\n')}`;
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const stamp = new Date().toISOString().slice(0, 10);

                    link.href = url;
                    link.download = `usuarios_${stamp}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);

                    this.pushToast('success', 'Tabela de usuarios exportada com sucesso.');
                },

                normalizeTeamName(name) {
                    const normalized = (name ?? '').toString().replace(/^\s+/, '');
                    if (normalized === '') {
                        return '';
                    }

                    return normalized.charAt(0).toUpperCase() + normalized.slice(1);
                },

                setNewTeamName(name) {
                    this.newTeamName = this.normalizeTeamName(name);
                },

                teamCreateUsers() {
                    const usersModule = this.modules.find((module) => module.key === 'users') ?? null;
                    return usersModule?.items ?? [];
                },

                setAddTeamSupervisor(userKey) {
                    this.newTeamSupervisorKey = userKey || '';
                    if (this.newTeamSupervisorKey && !this.newTeamUserKeys.includes(this.newTeamSupervisorKey)) {
                        this.newTeamUserKeys = [...this.newTeamUserKeys, this.newTeamSupervisorKey];
                    }
                },

                isTeamCreateUserSelected(userKey) {
                    return this.newTeamUserKeys.includes(userKey);
                },

                toggleTeamCreateUser(userKey, checked) {
                    const selectedUsers = new Set(this.newTeamUserKeys);
                    if (checked) {
                        selectedUsers.add(userKey);
                    } else {
                        selectedUsers.delete(userKey);
                        if (this.newTeamSupervisorKey === userKey) {
                            this.newTeamSupervisorKey = '';
                        }
                    }

                    this.newTeamUserKeys = [...selectedUsers];
                },

                openAddTeamModal() {
                    if (!this.canCreateTeams()) {
                        this.pushToast('error', 'Voce nao tem permissao para criar equipes.');
                        return;
                    }

                    this.showAddTeamModal = true;
                    this.creatingTeam = false;
                    this.newTeamName = '';
                    this.newTeamSupervisorKey = '';
                    this.newTeamUserKeys = [];
                },

                closeAddTeamModal() {
                    this.showAddTeamModal = false;
                    this.creatingTeam = false;
                },

                async submitCreateTeam() {
                    if (!this.teamsCreateUrl) {
                        return;
                    }

                    const teamName = this.normalizeTeamName(this.newTeamName).trim();
                    if (teamName === '') {
                        this.pushToast('error', 'Informe o nome da equipe.');
                        return;
                    }

                    const usersByKey = new Map(this.teamCreateUsers().map((user) => [user.key, user]));
                    const supervisorUser = usersByKey.get(this.newTeamSupervisorKey) ?? null;
                    const supervisorUserId = supervisorUser?.user_id ?? null;
                    const userIds = [...new Set(
                        this.newTeamUserKeys
                            .map((userKey) => usersByKey.get(userKey)?.user_id ?? null)
                            .filter((userId) => userId !== null && userId !== undefined)
                    )];

                    this.creatingTeam = true;

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.teamsCreateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                nome: teamName,
                                supervisor_user_id: supervisorUserId,
                                user_ids: userIds,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao criar equipe.');
                        }

                        const message = payload?.message || 'Equipe criada com sucesso.';
                        this.persistToastForReload('success', message);
                        this.closeAddTeamModal();
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao criar equipe.';
                        this.pushToast('error', message);
                    } finally {
                        this.creatingTeam = false;
                    }
                },

                openAddUserModal() {
                    if (!this.canCreateUsers()) {
                        this.pushToast('error', 'Voce nao tem permissao para criar usuarios.');
                        return;
                    }

                    this.showAddUserModal = true;
                    this.creatingUser = false;
                    this.newUserName = '';
                    this.newUserPassword = '';
                    this.showNewUserPassword = false;
                    this.newUserRoleKey = this.hierarchyOptions[0]?.key ?? '';
                    this.newUserTeamKey = '';
                    this.addUserCreateTeam = false;
                    this.addUserTeamName = '';
                    this.addUserTeamSupervisorKey = '';
                    this.addUserTeamUserKeys = [];
                },

                closeAddUserModal() {
                    this.showAddUserModal = false;
                    this.creatingUser = false;
                },

                setNewUserName(name) {
                    this.newUserName = (name ?? '').toString().replace(/^\s+/, '');
                },

                toggleNewUserPasswordVisibility() {
                    this.showNewUserPassword = !this.showNewUserPassword;
                },

                setAddUserCreateTeam(enabled) {
                    this.addUserCreateTeam = Boolean(enabled);
                    if (!this.addUserCreateTeam) {
                        this.addUserTeamName = '';
                        this.addUserTeamSupervisorKey = '';
                        this.addUserTeamUserKeys = [];
                    }
                },

                setAddUserTeamName(name) {
                    this.addUserTeamName = this.normalizeTeamName(name);
                },

                setAddUserTeamSupervisor(userKey) {
                    this.addUserTeamSupervisorKey = userKey || '';
                    if (this.addUserTeamSupervisorKey && !this.addUserTeamUserKeys.includes(this.addUserTeamSupervisorKey)) {
                        this.addUserTeamUserKeys = [...this.addUserTeamUserKeys, this.addUserTeamSupervisorKey];
                    }
                },

                isAddUserTeamUserSelected(userKey) {
                    return this.addUserTeamUserKeys.includes(userKey);
                },

                toggleAddUserTeamUser(userKey, checked) {
                    const selectedUsers = new Set(this.addUserTeamUserKeys);
                    if (checked) {
                        selectedUsers.add(userKey);
                    } else {
                        selectedUsers.delete(userKey);
                        if (this.addUserTeamSupervisorKey === userKey) {
                            this.addUserTeamSupervisorKey = '';
                        }
                    }

                    this.addUserTeamUserKeys = [...selectedUsers];
                },

                async submitCreateUser() {
                    if (!this.usersCreateUrl) {
                        return;
                    }

                    const userName = this.newUserName.trim();
                    if (userName === '') {
                        this.pushToast('error', 'Informe o nome do usuario.');
                        return;
                    }

                    const userPassword = (this.newUserPassword ?? '').toString();
                    if (userPassword.length < 6) {
                        this.pushToast('error', 'A senha deve ter pelo menos 6 caracteres.');
                        return;
                    }

                    const selectedRole = this.hierarchyOptions.find((role) => role.key === this.newUserRoleKey) ?? null;
                    const roleId = selectedRole?.role_id ?? null;
                    if (!roleId) {
                        this.pushToast('error', 'Selecione a hierarquia do usuario.');
                        return;
                    }

                    const selectedTeam = this.teamOptions.find((team) => team.key === this.newUserTeamKey) ?? null;
                    let equipeId = selectedTeam?.team_id ?? null;

                    const usersByKey = new Map(this.teamCreateUsers().map((user) => [user.key, user]));
                    const payload = {
                        nome: userName,
                        senha: userPassword,
                        role_id: roleId,
                        equipe_id: equipeId,
                        create_team: this.addUserCreateTeam,
                    };

                    if (this.addUserCreateTeam) {
                        const teamName = this.normalizeTeamName(this.addUserTeamName).trim();
                        if (teamName === '') {
                            this.pushToast('error', 'Informe o nome da nova equipe.');
                            return;
                        }

                        const supervisorUser = usersByKey.get(this.addUserTeamSupervisorKey) ?? null;
                        const supervisorUserId = supervisorUser?.user_id ?? null;
                        const userIds = [...new Set(
                            this.addUserTeamUserKeys
                                .map((userKey) => usersByKey.get(userKey)?.user_id ?? null)
                                .filter((userId) => userId !== null && userId !== undefined)
                        )];

                        payload.equipe_id = null;
                        payload.nova_equipe = {
                            nome: teamName,
                            supervisor_user_id: supervisorUserId,
                            user_ids: userIds,
                        };
                    }

                    this.creatingUser = true;

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.usersCreateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify(payload),
                        });

                        const responsePayload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(responsePayload?.message || 'Falha ao criar usuario.');
                        }

                        const message = responsePayload?.message || 'Usuario criado com sucesso.';
                        this.persistToastForReload('success', message);
                        this.closeAddUserModal();
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao criar usuario.';
                        this.pushToast('error', message);
                    } finally {
                        this.creatingUser = false;
                    }
                },

                collapseAllPageNodes() {
                    this.expandedPageNodes = {};
                },

                scrollPageToTop() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth',
                    });
                },

                selectModule(moduleKey) {
                    this.permissionSaveStatus = '';
                    this.permissionSaveMessage = '';
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.showResetPasswordModal = false;
                    this.showDeleteUserModal = false;
                    this.showDeleteTeamModal = false;
                    this.showAddTeamModal = false;
                    this.showAddUserModal = false;
                    this.resetPasswordValue = '';

                    if (this.activeModuleKey === moduleKey) {
                        this.activeModuleKey = null;
                        this.activeItemKey = null;
                        return;
                    }

                    this.activeModuleKey = moduleKey;
                    if (moduleKey === 'permissions') {
                        this.collapseAllPageNodes();
                    }
                    this.ensureSelectedItem();
                    this.triggerFan();
                },

                selectItem(itemKey) {
                    const shouldScrollToTop = this.isUsersModule() || this.isTeamsModule();
                    this.permissionSaveStatus = '';
                    this.permissionSaveMessage = '';
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.showResetPasswordModal = false;
                    this.showDeleteUserModal = false;
                    this.showDeleteTeamModal = false;
                    this.showAddTeamModal = false;
                    this.showAddUserModal = false;
                    this.resetPasswordValue = '';
                    this.activeItemKey = itemKey;
                    if (this.isUsersModule()) {
                        this.ensureUserTeamSelection(itemKey);
                        this.ensureUserNameSelection(itemKey);
                        this.ensureUserRoleSelection(itemKey);
                    }
                    if (this.isTeamsModule()) {
                        this.syncActiveTeamDraft();
                    }
                    if (this.isPermissionsModule()) {
                        this.collapseAllPageNodes();
                    }
                    if (shouldScrollToTop) {
                        this.scrollPageToTop();
                    }
                },

                isExpanded() {
                    return Boolean(this.activeModuleKey);
                },

                activeModule() {
                    return this.modules.find((module) => module.key === this.activeModuleKey) ?? null;
                },

                activeItems() {
                    const module = this.activeModule();
                    if (!module) {
                        return [];
                    }

                    const items = Array.isArray(module.items) ? module.items : [];
                    const searchTerm = this.normalizedFilterSearch();

                    if (module.key === 'users') {
                        return items.filter((item) => this.userMatchesFilters(item, searchTerm));
                    }

                    if (module.key === 'teams') {
                        return items.filter((item) => this.teamMatchesFilters(item, searchTerm));
                    }

                    if (searchTerm === '') {
                        return items;
                    }

                    return items.filter((item) => this.matchesSearchTerm(item, searchTerm));
                },

                usersModule() {
                    return this.modules.find((module) => module.key === 'users') ?? null;
                },

                userItemByKey(userKey) {
                    if (!userKey) {
                        return null;
                    }

                    const usersModule = this.usersModule();
                    const usersItems = Array.isArray(usersModule?.items) ? usersModule.items : [];
                    return usersItems.find((item) => item.key === userKey) ?? null;
                },

                isTeamsModule() {
                    return this.activeModuleKey === 'teams';
                },

                isUsersModule() {
                    return this.activeModuleKey === 'users';
                },

                isRegistersModule() {
                    return this.activeModuleKey === 'registers';
                },

                isPermissionsModule() {
                    return this.activeModuleKey === 'permissions';
                },

                activePermissionRole() {
                    if (!this.isPermissionsModule() || !this.activeItemKey) {
                        return null;
                    }

                    return this.activeItems().find((item) => item.key === this.activeItemKey) ?? null;
                },

                isMasterRoleSelected() {
                    const role = this.activePermissionRole();
                    return ((role?.slug ?? '').toLowerCase() === 'master');
                },

                activeUser() {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return null;
                    }

                    return this.userItemByKey(this.activeItemKey);
                },

                isCurrentUserInactive() {
                    return !Boolean(this.activeUser()?.is_active ?? true);
                },

                isCurrentUserSelf() {
                    const currentId = Number(this.authUserId ?? 0);
                    const selectedId = Number(this.activeUser()?.user_id ?? 0);
                    return currentId > 0 && selectedId > 0 && currentId === selectedId;
                },

                async toggleCurrentUserInactive(inactive) {
                    if (!this.isUsersModule() || !this.usersStatusSaveUrl || !this.canEditUsers()) {
                        return;
                    }

                    if (this.isCurrentUserSelf() && Boolean(inactive)) {
                        const message = 'Nao e permitido inativar o usuario logado.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const user = this.activeUser();
                    if (!user || user.user_id === null || user.user_id === undefined) {
                        const message = 'Usuario invalido para atualizar status.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const previousActive = Boolean(user.is_active ?? true);
                    const nextActive = !Boolean(inactive);

                    this.savingUserStatus = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    user.is_active = nextActive;

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.usersStatusSaveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                user_id: user.user_id,
                                ativo: nextActive,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao atualizar status do usuario.');
                        }

                        const message = payload?.message || 'Status do usuario atualizado.';
                        this.userTeamSaveStatus = 'success';
                        this.userTeamSaveMessage = message;
                        this.pushToast('success', message);
                    } catch (error) {
                        user.is_active = previousActive;
                        const message = error?.message || 'Falha ao atualizar status do usuario.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.savingUserStatus = false;
                    }
                },

                activeTeam() {
                    if (!this.isTeamsModule() || !this.activeItemKey) {
                        return null;
                    }

                    return this.activeItems().find((item) => item.key === this.activeItemKey) ?? null;
                },

                activeTeamMembers() {
                    if (!this.isTeamsModule() || !this.activeItemKey) {
                        return [];
                    }

                    return this.teamMembersByTeam[this.activeItemKey] ?? [];
                },

                openUserFromTeam(userKey) {
                    if (!userKey) {
                        return;
                    }

                    this.activeModuleKey = 'users';
                    this.activeItemKey = userKey;
                    this.ensureSelectedItem();
                    this.triggerFan();
                    this.scrollPageToTop();
                },

                syncActiveTeamDraft() {
                    const team = this.activeTeam();
                    this.activeTeamNameDraft = team?.label ?? '';
                },

                canRenameActiveTeam() {
                    const team = this.activeTeam();
                    return Boolean(team && team.team_id !== null && team.team_id !== undefined);
                },

                isOwnActiveTeam() {
                    const team = this.activeTeam();
                    const selectedTeamId = team?.team_id ?? null;
                    const currentUserTeamId = this.authUserTeamId ?? null;
                    if (selectedTeamId === null || selectedTeamId === undefined) {
                        return false;
                    }

                    if (currentUserTeamId === null || currentUserTeamId === undefined) {
                        return false;
                    }

                    return Number(selectedTeamId) === Number(currentUserTeamId);
                },

                canDeleteActiveTeam() {
                    const team = this.activeTeam();
                    if (!team || team.team_id === null || team.team_id === undefined) {
                        return false;
                    }

                    return this.canEditTeams() && !this.isOwnActiveTeam();
                },

                openDeleteTeamModal() {
                    if (!this.isTeamsModule() || !this.activeTeam()) {
                        return;
                    }

                    if (!this.canDeleteActiveTeam()) {
                        const message = this.isOwnActiveTeam()
                            ? 'Nao e permitido excluir a propria equipe.'
                            : 'Essa equipe nao pode ser excluida.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    this.showDeleteTeamModal = true;
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';
                },

                closeDeleteTeamModal() {
                    this.showDeleteTeamModal = false;
                },

                async confirmDeleteTeam() {
                    if (!this.isTeamsModule() || !this.teamsDeleteUrl) {
                        return;
                    }

                    const team = this.activeTeam();
                    if (!team || team.team_id === null || team.team_id === undefined) {
                        const message = 'Equipe invalida para exclusao.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    if (this.isOwnActiveTeam()) {
                        const message = 'Nao e permitido excluir a propria equipe.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                        this.closeDeleteTeamModal();
                        return;
                    }

                    this.deletingTeam = true;
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.teamsDeleteUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                team_id: team.team_id,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao excluir equipe.');
                        }

                        const message = payload?.message || 'Equipe excluida com sucesso.';
                        this.teamSaveStatus = 'success';
                        this.teamSaveMessage = message;
                        this.closeDeleteTeamModal();
                        this.persistToastForReload('success', message);
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao excluir equipe.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.deletingTeam = false;
                    }
                },

                activePageRows() {
                    if (this.isTeamsModule() || this.isUsersModule() || this.isRegistersModule()) {
                        return [];
                    }

                    if (!this.activeModule() || !this.activeItemKey) {
                        return [];
                    }

                    return this.flattenPageRows(this.pagesCatalog);
                },

                flattenPageRows(nodes, depth = 0) {
                    const rows = [];

                    nodes.forEach((node) => {
                        const hasChildren = Array.isArray(node.children) && node.children.length > 0;

                        rows.push({
                            key: node.key,
                            label: node.label,
                            depth,
                            hasChildren,
                            permissionSlug: node.permission_slug ?? null,
                        });

                        if (hasChildren && this.isPageNodeExpanded(node.key)) {
                            rows.push(...this.flattenPageRows(node.children, depth + 1));
                        }
                    });

                    return rows;
                },

                isPageNodeExpanded(pageKey) {
                    return Boolean(this.expandedPageNodes[pageKey]);
                },

                togglePageNode(pageKey) {
                    this.expandedPageNodes[pageKey] = !this.isPageNodeExpanded(pageKey);
                },

                expandAllPageNodes(nodes = this.pagesCatalog) {
                    nodes.forEach((node) => {
                        if (Array.isArray(node.children) && node.children.length > 0) {
                            this.expandedPageNodes[node.key] = true;
                            this.expandAllPageNodes(node.children);
                        }
                    });
                },

                findPageNode(pageKey, nodes = this.pagesCatalog) {
                    for (const node of nodes) {
                        if (node.key === pageKey) {
                            return node;
                        }

                        if (Array.isArray(node.children) && node.children.length > 0) {
                            const found = this.findPageNode(pageKey, node.children);
                            if (found) {
                                return found;
                            }
                        }
                    }

                    return null;
                },

                collectPermissionKeys(node) {
                    let keys = [];

                    if (node?.permission_slug) {
                        keys.push(node.key);
                    }

                    if (Array.isArray(node?.children) && node.children.length > 0) {
                        node.children.forEach((child) => {
                            keys.push(...this.collectPermissionKeys(child));
                        });
                    }

                    return [...new Set(keys)];
                },

                permissionRows() {
                    const rows = [];

                    const walk = (nodes) => {
                        nodes.forEach((node) => {
                            if (node.permission_slug) {
                                rows.push({
                                    key: node.key,
                                    permissionSlug: node.permission_slug,
                                });
                            }

                            if (Array.isArray(node.children) && node.children.length > 0) {
                                walk(node.children);
                            }
                        });
                    };

                    walk(this.pagesCatalog);

                    return rows;
                },

                currentSelectionKey() {
                    if (!this.activeModuleKey || !this.activeItemKey || this.isTeamsModule() || this.isUsersModule()) {
                        return null;
                    }

                    return `${this.activeModuleKey}:${this.activeItemKey}`;
                },

                ensureSelectionBucket() {
                    const key = this.currentSelectionKey();
                    if (!key) {
                        return;
                    }

                    if (!this.enabledPagesBySelection[key]) {
                        this.enabledPagesBySelection[key] = {};
                    }
                },

                isPageEnabled(pageKey) {
                    const key = this.currentSelectionKey();
                    if (!key) {
                        return false;
                    }

                    const selection = this.enabledPagesBySelection[key] ?? {};
                    const node = this.findPageNode(pageKey);
                    if (!node) {
                        return false;
                    }

                    if (Array.isArray(node.children) && node.children.length > 0) {
                        const descendantKeys = this.collectPermissionKeys(node);
                        if (descendantKeys.length === 0) {
                            return Boolean(selection[pageKey]);
                        }

                        return descendantKeys.every((descendantKey) => Boolean(selection[descendantKey]));
                    }

                    return Boolean(selection[pageKey]);
                },

                pageCounterLabel(pageKey) {
                    const node = this.findPageNode(pageKey);
                    if (!node || !Array.isArray(node.children) || node.children.length === 0) {
                        return '';
                    }

                    const key = this.currentSelectionKey();
                    const selection = key ? (this.enabledPagesBySelection[key] ?? {}) : {};

                    const countNodeAsActive = (currentNode) => {
                        if (!currentNode) {
                            return false;
                        }

                        if (currentNode.permission_slug) {
                            return Boolean(selection[currentNode.key]);
                        }

                        if (Array.isArray(currentNode.children) && currentNode.children.length > 0) {
                            return currentNode.children.some((childNode) => countNodeAsActive(childNode));
                        }

                        return false;
                    };

                    const countableChildren = node.children.filter((childNode) => {
                        if (childNode.permission_slug) {
                            return true;
                        }

                        return Array.isArray(childNode.children) && childNode.children.length > 0;
                    });

                    if (countableChildren.length === 0) {
                        return '';
                    }

                    const enabledCount = countableChildren.reduce((count, childNode) => {
                        return count + (countNodeAsActive(childNode) ? 1 : 0);
                    }, 0);

                    return `${enabledCount}/${countableChildren.length}`;
                },

                togglePage(pageKey, enabled) {
                    this.ensureSelectionBucket();
                    const key = this.currentSelectionKey();
                    if (!key) {
                        return;
                    }

                    const selection = this.enabledPagesBySelection[key];
                    const node = this.findPageNode(pageKey);
                    if (!node) {
                        selection[pageKey] = enabled;
                        return;
                    }

                    const descendantKeys = this.collectPermissionKeys(node);
                    if (descendantKeys.length > 0 && Array.isArray(node.children) && node.children.length > 0) {
                        descendantKeys.forEach((descendantKey) => {
                            selection[descendantKey] = enabled;
                        });
                        return;
                    }

                    selection[pageKey] = enabled;
                },

                currentUserNameSelection() {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return '';
                    }

                    return this.usersNameSelection[this.activeItemKey] ?? '';
                },

                setCurrentUserNameSelection(userName) {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return;
                    }

                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.usersNameSelection[this.activeItemKey] = userName;
                },

                currentUserTeamSelection() {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return '';
                    }

                    const selectedTeam = this.usersTeamSelection[this.activeItemKey];
                    if (selectedTeam !== undefined) {
                        return selectedTeam;
                    }

                    return this.teamKeyForUser(this.activeUser());
                },

                setCurrentUserTeamSelection(teamKey) {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return;
                    }

                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.usersTeamSelection[this.activeItemKey] = teamKey;
                },

                currentUserRoleSelection() {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return '';
                    }

                    const selectedRole = this.usersRoleSelection[this.activeItemKey];
                    if (selectedRole !== undefined) {
                        return selectedRole;
                    }

                    return this.roleKeyForUser(this.activeUser());
                },

                teamKeyForUser(user) {
                    if (!user) {
                        return '';
                    }

                    const optionKeys = new Set(this.teamOptions.map((team) => team.key));
                    const rawTeamKey = (user?.team_key ?? '').toString();
                    if (rawTeamKey !== '' && optionKeys.has(rawTeamKey)) {
                        return rawTeamKey;
                    }

                    const userTeamId = user?.team_id ?? null;
                    if (userTeamId !== null && userTeamId !== undefined) {
                        const matchedTeam = this.teamOptions.find((team) => Number(team.team_id) === Number(userTeamId));
                        if (matchedTeam?.key) {
                            return matchedTeam.key;
                        }

                        const fallbackKey = `team-${userTeamId}`;
                        if (optionKeys.has(fallbackKey)) {
                            return fallbackKey;
                        }
                    }

                    return '';
                },

                roleKeyForUser(user) {
                    if (!user) {
                        return '';
                    }

                    const optionKeys = new Set(this.hierarchyOptions.map((role) => role.key));
                    const userRoleId = user?.role_id ?? null;
                    if (userRoleId !== null && userRoleId !== undefined) {
                        const matchedRole = this.hierarchyOptions.find((role) => Number(role.role_id) === Number(userRoleId));
                        if (matchedRole?.key) {
                            return matchedRole.key;
                        }

                        const fallbackKey = `role-${userRoleId}`;
                        if (optionKeys.has(fallbackKey)) {
                            return fallbackKey;
                        }
                    }

                    return '';
                },

                activeUserTeamLabel() {
                    const selectedKey = this.currentUserTeamSelection();
                    const option = this.teamOptions.find((team) => team.key === selectedKey) ?? null;
                    if (option?.label) {
                        return option.label;
                    }

                    const user = this.activeUser();
                    const fallbackLabel = (user?.team_label ?? '').toString().trim();
                    return fallbackLabel !== '' ? fallbackLabel : 'Sem equipe';
                },

                activeUserRoleLabel() {
                    const selectedKey = this.currentUserRoleSelection();
                    const option = this.hierarchyOptions.find((role) => role.key === selectedKey) ?? null;
                    if (option?.displayLabel) {
                        return option.displayLabel;
                    }

                    const user = this.activeUser();
                    const roleLabel = (user?.role_label ?? '').toString().trim();
                    const roleLevel = user?.role_nivel ?? null;
                    if (roleLabel !== '') {
                        return roleLevel !== null && roleLevel !== undefined
                            ? `${roleLabel} (Nivel ${roleLevel})`
                            : roleLabel;
                    }

                    return 'Sem hierarquia';
                },

                setCurrentUserRoleSelection(roleKey) {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return;
                    }

                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.usersRoleSelection[this.activeItemKey] = roleKey;
                },

                ensureUserTeamSelection(userKey) {
                    if (!userKey) {
                        return;
                    }

                    const user = this.userItemByKey(userKey);
                    const defaultTeamKey = this.teamKeyForUser(user);
                    const optionKeys = this.teamOptions.map((team) => team.key);

                    const currentTeamKey = this.usersTeamSelection[userKey];
                    const hasCurrent = typeof currentTeamKey === 'string' && optionKeys.includes(currentTeamKey);
                    const shouldApplyDefault = currentTeamKey === undefined
                        || (!hasCurrent && (currentTeamKey !== '' || defaultTeamKey !== ''));

                    if (shouldApplyDefault) {
                        this.usersTeamSelection[userKey] = defaultTeamKey;
                    }
                },

                ensureUserRoleSelection(userKey) {
                    if (!userKey) {
                        return;
                    }

                    const user = this.userItemByKey(userKey);
                    const optionKeys = this.hierarchyOptions.map((role) => role.key);
                    const defaultRoleKey = this.roleKeyForUser(user);

                    const currentRoleKey = this.usersRoleSelection[userKey];
                    const hasCurrent = typeof currentRoleKey === 'string' && optionKeys.includes(currentRoleKey);
                    const shouldApplyDefault = currentRoleKey === undefined
                        || (!hasCurrent && (currentRoleKey !== '' || defaultRoleKey !== ''));

                    if (shouldApplyDefault) {
                        this.usersRoleSelection[userKey] = defaultRoleKey;
                    }
                },

                ensureUserNameSelection(userKey) {
                    if (!this.isUsersModule() || !userKey) {
                        return;
                    }

                    if (this.usersNameSelection[userKey] === undefined) {
                        const user = this.activeItems().find((item) => item.key === userKey) ?? null;
                        const userName = (user?.name ?? '').toString().trim();
                        const fallbackLogin = (user?.login ?? '').toString().trim();
                        this.usersNameSelection[userKey] = userName !== '' ? userName : fallbackLogin;
                    }
                },

                async saveCurrentUserTeam() {
                    if (!this.isUsersModule() || !this.activeItemKey || !this.usersSaveTeamUrl || !this.canEditUsers()) {
                        return;
                    }

                    const user = this.activeUser();
                    if (!user || user.user_id === null || user.user_id === undefined) {
                        const message = 'Usuario invalido para salvar.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const userName = this.currentUserNameSelection().trim();
                    if (userName === '') {
                        const message = 'Informe o nome do usuario.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const selectedTeamKey = this.currentUserTeamSelection();
                    const selectedTeam = this.teamOptions.find((team) => team.key === selectedTeamKey) ?? null;
                    const equipeId = selectedTeam?.team_id ?? null;
                    const selectedRoleKey = this.currentUserRoleSelection();
                    const selectedRole = this.hierarchyOptions.find((role) => role.key === selectedRoleKey) ?? null;
                    const roleId = selectedRole?.role_id ?? null;

                    this.savingUserTeam = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';

                    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';

                    try {
                        const response = await fetch(this.usersSaveTeamUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                user_id: user.user_id,
                                equipe_id: equipeId,
                                role_id: roleId,
                                nome: userName,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao salvar dados do usuario.');
                        }

                        const message = payload?.message || 'Dados do usuario salvos com sucesso.';
                        this.userTeamSaveStatus = 'success';
                        this.userTeamSaveMessage = message;
                        this.persistToastForReload('success', message);
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao salvar dados do usuario.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.savingUserTeam = false;
                    }
                },

                openResetPasswordModal() {
                    if (!this.isUsersModule() || !this.activeUser() || !this.canEditUsers()) {
                        return;
                    }

                    this.resetPasswordValue = '';
                    this.showResetPasswordModal = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                },

                closeResetPasswordModal() {
                    this.showResetPasswordModal = false;
                    this.resetPasswordValue = '';
                },

                async submitResetPassword() {
                    if (!this.isUsersModule() || !this.usersResetPasswordUrl || !this.canEditUsers()) {
                        return;
                    }

                    const user = this.activeUser();
                    if (!user || user.user_id === null || user.user_id === undefined) {
                        const message = 'Usuario invalido para redefinir senha.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const newPassword = (this.resetPasswordValue ?? '').trim();
                    if (newPassword.length < 6) {
                        const message = 'A nova senha deve ter pelo menos 6 caracteres.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    this.resettingPassword = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.usersResetPasswordUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                user_id: user.user_id,
                                nova_senha: newPassword,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao redefinir senha.');
                        }

                        const message = payload?.message || 'Senha redefinida com sucesso.';
                        this.userTeamSaveStatus = 'success';
                        this.userTeamSaveMessage = message;
                        this.pushToast('success', message);
                        this.closeResetPasswordModal();
                    } catch (error) {
                        const message = error?.message || 'Falha ao redefinir senha.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.resettingPassword = false;
                    }
                },

                openDeleteUserModal() {
                    if (!this.isUsersModule() || !this.activeUser() || !this.canDeleteUsers()) {
                        return;
                    }

                    this.showDeleteUserModal = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                },

                closeDeleteUserModal() {
                    this.showDeleteUserModal = false;
                },

                async confirmDeleteUser() {
                    if (!this.isUsersModule() || !this.usersDeleteUrl || !this.canDeleteUsers()) {
                        return;
                    }

                    const user = this.activeUser();
                    if (!user || user.user_id === null || user.user_id === undefined) {
                        const message = 'Usuario invalido para exclusao.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    this.deletingUser = true;
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.usersDeleteUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                user_id: user.user_id,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao excluir usuario.');
                        }

                        const message = payload?.message || 'Usuario excluido com sucesso.';
                        this.userTeamSaveStatus = 'success';
                        this.userTeamSaveMessage = message;
                        this.closeDeleteUserModal();
                        this.persistToastForReload('success', message);
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao excluir usuario.';
                        this.userTeamSaveStatus = 'error';
                        this.userTeamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.deletingUser = false;
                    }
                },

                async saveActiveTeamName() {
                    if (!this.isTeamsModule() || !this.teamsRenameUrl || !this.canEditTeams()) {
                        return;
                    }

                    const team = this.activeTeam();
                    if (!team || team.team_id === null || team.team_id === undefined) {
                        const message = 'Essa equipe nao pode ser renomeada.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const teamName = (this.activeTeamNameDraft ?? '').trim();
                    if (teamName === '') {
                        const message = 'Informe o nome da equipe.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    this.savingTeamName = true;
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.teamsRenameUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                team_id: team.team_id,
                                nome: teamName,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao salvar a equipe.');
                        }

                        const message = payload?.message || 'Equipe atualizada com sucesso.';
                        this.teamSaveStatus = 'success';
                        this.teamSaveMessage = message;
                        this.persistToastForReload('success', message);
                        window.location.reload();
                    } catch (error) {
                        const message = error?.message || 'Falha ao salvar a equipe.';
                        this.teamSaveStatus = 'error';
                        this.teamSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.savingTeamName = false;
                    }
                },

                async saveRolePermissions() {
                    if (!this.isPermissionsModule() || !this.permissionsSaveUrl || !this.canEditConfig()) {
                        return;
                    }

                    const role = this.activePermissionRole();
                    if (!role?.role_id) {
                        const message = 'Perfil invalido para salvar.';
                        this.permissionSaveStatus = 'error';
                        this.permissionSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    const selectionKey = this.currentSelectionKey();
                    if (!selectionKey) {
                        const message = 'Selecione um perfil.';
                        this.permissionSaveStatus = 'error';
                        this.permissionSaveMessage = message;
                        this.pushToast('error', message);
                        return;
                    }

                    this.ensureSelectionBucket();
                    const selectedPermissions = this.enabledPagesBySelection[selectionKey] ?? {};
                    const payloadPermissions = {};

                    this.permissionRows().forEach((permissionRow) => {
                        payloadPermissions[permissionRow.permissionSlug] = Boolean(selectedPermissions[permissionRow.key]);
                    });

                    this.savingPermissions = true;
                    this.permissionSaveStatus = '';
                    this.permissionSaveMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') ?? '';
                        const response = await fetch(this.permissionsSaveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                role_id: role.role_id,
                                permissions: payloadPermissions,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Falha ao salvar permissoes.');
                        }

                        const message = payload?.message || 'Permissoes salvas com sucesso.';
                        this.permissionSaveStatus = 'success';
                        this.permissionSaveMessage = message;
                        this.pushToast('success', message);
                    } catch (error) {
                        const message = error?.message || 'Falha ao salvar permissoes.';
                        this.permissionSaveStatus = 'error';
                        this.permissionSaveMessage = message;
                        this.pushToast('error', message);
                    } finally {
                        this.savingPermissions = false;
                    }
                },

                activeModuleColumn2Title() {
                    const module = this.activeModule();
                    return module ? module.column2Title : 'Sem dados';
                },

                activeModuleColumn3Title() {
                    const module = this.activeModule();
                    return module ? module.column3Title : 'Sem dados';
                },

                ensureSelectedItem() {
                    const items = this.activeItems();
                    if (items.length === 0) {
                        this.activeItemKey = null;
                        return;
                    }

                    const hasSelection = items.some((item) => item.key === this.activeItemKey);
                    if (!hasSelection) {
                        this.activeItemKey = items[0].key;
                    }

                    if (!this.isTeamsModule() && !this.isUsersModule()) {
                        this.ensureSelectionBucket();
                    }

                    if (this.isUsersModule()) {
                        this.ensureUserTeamSelection(this.activeItemKey);
                        this.ensureUserNameSelection(this.activeItemKey);
                        this.ensureUserRoleSelection(this.activeItemKey);
                    }

                    if (this.isTeamsModule()) {
                        this.syncActiveTeamDraft();
                    }

                    if (this.isPermissionsModule()) {
                        this.collapseAllPageNodes();
                    }
                },

                triggerFan() {
                    this.fanPulse = false;
                    this.$nextTick(() => {
                        this.fanPulse = true;
                        setTimeout(() => {
                            this.fanPulse = false;
                        }, 340);
                    });
                }
            };
        }
    </script>
</x-app-layout>
