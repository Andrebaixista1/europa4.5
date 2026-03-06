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
            grid-template-columns: minmax(220px, 1fr) minmax(260px, 1fr) minmax(260px, 1fr);
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
            width: 100%;
            text-align: left;
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
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            cursor: pointer;
        }

        .settings-member-level {
            flex: 0 0 auto;
            border: 1px solid var(--settings-border);
            border-radius: 2px;
            padding: 0.12rem 0.35rem;
            font-size: 0.72rem;
            color: var(--settings-muted);
            white-space: nowrap;
        }

        .settings-user-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
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
            background: var(--settings-surface);
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
            background: rgba(20, 83, 45, 0.28);
        }

        .settings-toast--error {
            border-color: #7f1d1d;
            background: rgba(127, 29, 29, 0.26);
        }

        .settings-toast--info {
            border-color: #1d4ed8;
            background: rgba(29, 78, 216, 0.2);
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
                            <label class="settings-filter-label" for="settings-status">Status</label>
                            <select id="settings-status" class="settings-filter-select" x-model="filterStatus">
                                <option value="">Todos</option>
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
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

                            <button type="button" class="settings-add-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <path d="M20 8v6"></path>
                                    <path d="M17 11h6"></path>
                                </svg>
                                <span>Adicionar Usu&aacute;rio</span>
                            </button>

                            <button type="button" class="settings-add-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Adicionar Equipe</span>
                            </button>

                            <button type="button" class="settings-add-btn">
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
                                                x-on:input="setCurrentUserNameSelection($event.target.value)"
                                            >
                                            <span class="settings-member-level">Selecionado</span>
                                        </div>

                                        <button
                                            type="button"
                                            class="settings-action-btn settings-user-action block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition"
                                            x-on:click="openResetPasswordModal()"
                                        >
                                            <span>Resetar senha</span>
                                        </button>

                                        <div class="settings-action-btn settings-user-toggle-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <span>Inativar usu&aacute;rio</span>
                                            <label class="settings-switch">
                                                <input
                                                    type="checkbox"
                                                    :checked="isCurrentUserInactive()"
                                                    :disabled="savingUserStatus"
                                                    x-on:change="toggleCurrentUserInactive($event.target.checked)"
                                                >
                                                <span class="settings-switch-track">
                                                    <span class="settings-switch-thumb"></span>
                                                </span>
                                            </label>
                                        </div>

                                        <button
                                            type="button"
                                            class="settings-action-btn settings-user-action settings-user-action--danger block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium transition"
                                            x-on:click="openDeleteUserModal()"
                                        >
                                            <span>Excluir usu&aacute;rio</span>
                                        </button>

                                        <div class="settings-action-btn settings-user-team-row block w-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-800 transition">
                                            <span class="settings-user-team-label">Alterar equipe</span>
                                            <select
                                                class="settings-filter-select settings-user-team-select"
                                                :value="currentUserTeamSelection()"
                                                x-on:change="setCurrentUserTeamSelection($event.target.value)"
                                            >
                                                <option value="">Sem equipe</option>
                                                <template x-for="team in teamOptions" :key="team.key">
                                                    <option :value="team.key" x-text="team.label"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <button
                                            type="button"
                                            class="settings-user-save-btn"
                                            :disabled="savingUserTeam"
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
                                                >
                                                <button
                                                    type="button"
                                                    class="settings-team-rename-save-btn"
                                                    :disabled="savingTeamName || !canRenameActiveTeam()"
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
                                                    :disabled="isPermissionsModule() && isMasterRoleSelected()"
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
                                            :disabled="savingPermissions || isMasterRoleSelected()"
                                            x-on:click="saveRolePermissions()"
                                            x-text="savingPermissions ? 'Salvando...' : (isMasterRoleSelected() ? 'Master sempre tem acesso total' : 'Salvar permissoes')"
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
            const usersSaveTeamUrl = @js($usersSaveTeamUrl ?? '');
            const usersStatusSaveUrl = @js($usersStatusSaveUrl ?? '');
            const usersResetPasswordUrl = @js($usersResetPasswordUrl ?? '');
            const usersDeleteUrl = @js($usersDeleteUrl ?? '');

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
                teamOptions: teamsFromDb.length > 0
                    ? teamsFromDb.map((team) => ({
                        key: team.key,
                        label: team.label,
                        team_id: team.team_id ?? null,
                    }))
                    : [],
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
                filterSearch: '',
                filterStatus: '',
                filterCreatedAt: '',
                activeModuleKey: null,
                activeItemKey: null,
                fanPulse: false,
                permissionsSaveUrl: permissionsSaveUrl,
                teamsRenameUrl: teamsRenameUrl,
                usersSaveTeamUrl: usersSaveTeamUrl,
                usersStatusSaveUrl: usersStatusSaveUrl,
                usersResetPasswordUrl: usersResetPasswordUrl,
                usersDeleteUrl: usersDeleteUrl,
                permissionSaveStatus: '',
                permissionSaveMessage: '',
                savingPermissions: false,
                teamSaveStatus: '',
                teamSaveMessage: '',
                savingTeamName: false,
                activeTeamNameDraft: '',
                userTeamSaveStatus: '',
                userTeamSaveMessage: '',
                savingUserTeam: false,
                savingUserStatus: false,
                showResetPasswordModal: false,
                resetPasswordValue: '',
                resettingPassword: false,
                showDeleteUserModal: false,
                deletingUser: false,
                toasts: [],
                nextToastId: 1,
                toastTimers: {},
                pendingToastStorageKey: 'settings.pending-toast',

                init() {
                    this.ensureSelectedItem();
                    this.consumePendingToast();
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
                    this.filterStatus = '';
                    this.filterCreatedAt = '';
                },

                collapseAllPageNodes() {
                    this.expandedPageNodes = {};
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
                    this.permissionSaveStatus = '';
                    this.permissionSaveMessage = '';
                    this.teamSaveStatus = '';
                    this.teamSaveMessage = '';
                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.showResetPasswordModal = false;
                    this.showDeleteUserModal = false;
                    this.resetPasswordValue = '';
                    this.activeItemKey = itemKey;
                    if (this.isUsersModule()) {
                        this.ensureUserTeamSelection(itemKey);
                        this.ensureUserNameSelection(itemKey);
                    }
                    if (this.isTeamsModule()) {
                        this.syncActiveTeamDraft();
                    }
                    if (this.isPermissionsModule()) {
                        this.collapseAllPageNodes();
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
                    return module ? module.items : [];
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

                    return this.activeItems().find((item) => item.key === this.activeItemKey) ?? null;
                },

                isCurrentUserInactive() {
                    return !Boolean(this.activeUser()?.is_active ?? true);
                },

                async toggleCurrentUserInactive(inactive) {
                    if (!this.isUsersModule() || !this.usersStatusSaveUrl) {
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
                },

                syncActiveTeamDraft() {
                    const team = this.activeTeam();
                    this.activeTeamNameDraft = team?.label ?? '';
                },

                canRenameActiveTeam() {
                    const team = this.activeTeam();
                    return Boolean(team && team.team_id !== null && team.team_id !== undefined);
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

                    return this.usersTeamSelection[this.activeItemKey] ?? '';
                },

                setCurrentUserTeamSelection(teamKey) {
                    if (!this.isUsersModule() || !this.activeItemKey) {
                        return;
                    }

                    this.userTeamSaveStatus = '';
                    this.userTeamSaveMessage = '';
                    this.usersTeamSelection[this.activeItemKey] = teamKey;
                },

                ensureUserTeamSelection(userKey) {
                    if (!this.isUsersModule() || !userKey) {
                        return;
                    }

                    if (this.usersTeamSelection[userKey] === undefined) {
                        const user = this.activeItems().find((item) => item.key === userKey) ?? null;
                        let defaultTeamKey = user?.team_key ?? '';

                        if (!defaultTeamKey && ((user?.login ?? '').toLowerCase() === 'andrefelipe')) {
                            const generalTeam = this.teamOptions.find((team) => (team.label ?? '').toLowerCase() === 'equipe geral');
                            defaultTeamKey = generalTeam?.key ?? '';
                        }

                        this.usersTeamSelection[userKey] = defaultTeamKey;
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
                    if (!this.isUsersModule() || !this.activeItemKey || !this.usersSaveTeamUrl) {
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
                    if (!this.isUsersModule() || !this.activeUser()) {
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
                    if (!this.isUsersModule() || !this.usersResetPasswordUrl) {
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
                    if (!this.isUsersModule() || !this.activeUser()) {
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
                    if (!this.isUsersModule() || !this.usersDeleteUrl) {
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
                    if (!this.isTeamsModule() || !this.teamsRenameUrl) {
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
                    if (!this.isPermissionsModule() || !this.permissionsSaveUrl) {
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
