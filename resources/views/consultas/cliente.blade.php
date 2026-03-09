<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Consulta Cliente
        </h2>
    </x-slot>

    <style>
        .consulta-cliente-layout {
            display: block;
        }

        .consulta-cliente-left {
            border-bottom: 0;
        }

        .consulta-top-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.9rem;
        }

        @media (min-width: 1024px) {
            .consulta-top-grid {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                align-items: start;
            }
        }

        .consultas-dropdown {
            position: relative;
        }

        .consultas-dropdown__trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.375rem;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: rgb(31 41 55);
            background: rgb(255 255 255);
            transition: border-color 120ms ease, box-shadow 120ms ease;
        }

        .consultas-dropdown__trigger:hover {
            border-color: rgb(156 163 175);
        }

        .consultas-dropdown__trigger:focus-visible {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
        }

        .consultas-dropdown__menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.45rem);
            z-index: 80;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            background: rgb(255 255 255);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
            padding: 0.55rem;
            display: none;
        }

        .consultas-dropdown.is-open .consultas-dropdown__menu {
            display: block;
        }

        .consultas-dropdown__search {
            width: 100%;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.375rem;
            padding: 0.45rem 0.6rem;
            font-size: 0.86rem;
            color: rgb(31 41 55);
            background: rgb(255 255 255);
        }

        .consultas-dropdown__search:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .consultas-dropdown__actions {
            margin-top: 0.45rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem;
        }

        .consultas-dropdown__action {
            border: 1px solid rgb(209 213 219);
            border-radius: 0.3rem;
            background: rgb(255 255 255);
            color: rgb(31 41 55);
            font-size: 0.79rem;
            font-weight: 600;
            padding: 0.35rem 0.45rem;
            cursor: pointer;
        }

        .consultas-dropdown__action:hover {
            border-color: rgb(156 163 175);
        }

        .consultas-dropdown__list {
            margin-top: 0.5rem;
            max-height: 280px;
            overflow: auto;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.4rem;
        }

        .consultas-dropdown__option {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 0;
            border-bottom: 1px solid rgb(229 231 235);
            background: rgb(255 255 255);
            color: rgb(31 41 55);
            text-align: left;
            padding: 0.5rem 0.65rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        .consultas-dropdown__option:last-child {
            border-bottom: 0;
        }

        .consultas-dropdown__option:hover {
            background: rgb(249 250 251);
        }

        .consultas-dropdown__option.is-selected {
            background: rgba(0, 128, 255, 0.12);
        }

        .consultas-dropdown__check {
            opacity: 0;
            color: #0b7b26;
            font-weight: 700;
        }

        .consultas-dropdown__option.is-selected .consultas-dropdown__check {
            opacity: 1;
        }

        .consultas-dropdown__empty {
            padding: 0.65rem;
            text-align: center;
            font-size: 0.82rem;
            color: rgb(107 114 128);
        }

        html.theme-dark .consultas-dropdown__trigger,
        html.theme-dark .consultas-dropdown__menu,
        html.theme-dark .consultas-dropdown__search,
        html.theme-dark .consultas-dropdown__action,
        html.theme-dark .consultas-dropdown__option {
            background: rgb(10 12 16);
            color: rgb(229 231 235);
            border-color: rgb(55 65 81);
        }

        html.theme-dark .consultas-dropdown__option:hover {
            background: rgb(20 24 31);
        }

        html.theme-dark .consultas-dropdown__option.is-selected {
            background: rgba(0, 128, 255, 0.22);
        }

        html.theme-dark .consultas-dropdown__empty {
            color: rgb(156 163 175);
        }

        .consulta-result-stack {
            margin-top: 1rem;
            display: grid;
            gap: 0.8rem;
        }

        .consulta-result-card {
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            background: #fff;
            overflow: hidden;
        }

        .consulta-result-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgb(229 231 235);
        }

        .consulta-result-title {
            font-size: 0.95rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: rgb(31 41 55);
        }

        .consulta-result-grid-6 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.7rem;
            padding: 0.9rem 1rem;
        }

        .consulta-result-grid-5 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.7rem;
        }

        .consulta-result-grid-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .consulta-result-grid-2-wide {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.8rem;
        }

        .consulta-result-field__label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgb(107 114 128);
            font-weight: 700;
        }

        .consulta-result-field__value {
            display: block;
            margin-top: 0.15rem;
            color: rgb(17 24 39);
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.25;
            word-break: break-word;
        }

        .consulta-kpi {
            border: 1px solid rgb(229 231 235);
            border-radius: 0.6rem;
            background: #fff;
            padding: 0.75rem 0.85rem;
            min-height: 80px;
        }

        .consulta-kpi__label {
            font-size: 0.8rem;
            font-weight: 700;
            color: rgb(75 85 99);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .consulta-kpi__value {
            margin-top: 0.25rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: rgb(17 24 39);
        }

        .consulta-list-table {
            width: 100%;
            border-collapse: collapse;
        }

        .consulta-list-table th,
        .consulta-list-table td {
            border-bottom: 1px solid rgb(229 231 235);
            padding: 0.55rem 0.65rem;
            text-align: left;
            font-size: 0.88rem;
            white-space: nowrap;
        }

        .consulta-list-table th {
            background: rgb(248 250 252);
            color: rgb(55 65 81);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.72rem;
        }

        .consulta-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.7rem;
            height: 1.3rem;
            padding: 0 0.4rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 800;
            color: #111827;
            background: #f59e0b;
        }

        .consulta-copy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            border: 1px solid rgb(209 213 219);
            background: #fff;
            color: rgb(55 65 81);
            cursor: pointer;
            transition: border-color 120ms ease, color 120ms ease, background-color 120ms ease;
        }

        .consulta-copy-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
        }

        .consulta-module-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            padding: 0.6rem 0.7rem;
            border-bottom: 1px solid rgb(229 231 235);
        }

        .consulta-module-row:last-child {
            border-bottom: 0;
        }

        .consulta-module-row__name {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: rgb(31 41 55);
        }

        .consulta-module-row__count {
            font-size: 1rem;
            font-weight: 800;
            color: rgb(31 41 55);
        }

        .consulta-empty {
            padding: 0.9rem 1rem;
            color: rgb(107 114 128);
            font-size: 0.9rem;
        }

        @media (min-width: 780px) {
            .consulta-result-grid-5 {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }

            .consulta-result-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 980px) {
            .consulta-result-grid-6 {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .consulta-result-grid-2-wide {
                grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
            }
        }

        html.theme-dark .consulta-result-card,
        html.theme-dark .consulta-kpi {
            background: rgba(10, 12, 16, 0.94);
            border-color: rgb(55 65 81);
        }

        html.theme-dark .consulta-result-card__header {
            border-bottom-color: rgb(55 65 81);
        }

        html.theme-dark .consulta-result-title,
        html.theme-dark .consulta-result-field__value,
        html.theme-dark .consulta-kpi__value,
        html.theme-dark .consulta-module-row__name,
        html.theme-dark .consulta-module-row__count {
            color: rgb(229 231 235);
        }

        html.theme-dark .consulta-result-field__label,
        html.theme-dark .consulta-kpi__label,
        html.theme-dark .consulta-empty {
            color: rgb(156 163 175);
        }

        html.theme-dark .consulta-list-table th,
        html.theme-dark .consulta-list-table td,
        html.theme-dark .consulta-module-row {
            border-bottom-color: rgb(55 65 81);
        }

        html.theme-dark .consulta-list-table th {
            background: rgb(31 41 55);
            color: rgb(209 213 219);
        }

        html.theme-dark .consulta-copy-btn {
            background: rgb(10 12 16);
            border-color: rgb(75 85 99);
            color: rgb(209 213 219);
        }

        html.theme-dark .consulta-copy-btn:hover {
            border-color: rgb(107 114 128);
            color: rgb(243 244 246);
        }

        .dados-adicionais-panel {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgb(229 231 235);
            opacity: 0;
            transform-origin: top center;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 190ms ease, transform 260ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .consulta-cliente-layout.has-dados-adicionais .dados-adicionais-panel {
            display: block;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .dados-adicionais-panel.fan-animate {
            animation: consultaFanOpen 240ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        @keyframes consultaFanOpen {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .dados-adicionais-panel {
                transition: none;
            }

            .dados-adicionais-panel.fan-animate {
                animation: none;
            }
        }

        .consulta-toast-stack {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1200;
            display: grid;
            gap: 0.45rem;
            width: min(92vw, 360px);
        }

        .consulta-toast {
            border-radius: 4px;
            border: 1px solid rgb(55 65 81);
            background: rgba(8, 10, 12, 0.96);
            color: rgb(243 244 246);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
            padding: 0.65rem 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .consulta-toast__icon {
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

        .consulta-toast__text {
            flex: 1 1 auto;
            min-width: 0;
        }

        .consulta-toast__close {
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

        .consulta-toast__close:hover {
            opacity: 1;
        }

        .consulta-toast--success {
            border-color: #14532d;
            background: rgba(10, 45, 28, 0.92);
        }

        .consulta-toast--error {
            border-color: #7f1d1d;
            background: rgba(68, 17, 17, 0.92);
        }

        .consulta-toast--info {
            border-color: #1d4ed8;
            background: rgba(13, 34, 82, 0.92);
        }

        html.theme-light .consulta-toast {
            color: rgb(17 24 39);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.14);
        }

        html.theme-light .consulta-toast--success {
            border-color: #15803d;
            background: rgba(22, 163, 74, 0.15);
        }

        html.theme-light .consulta-toast--error {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.12);
        }

        html.theme-light .consulta-toast--info {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="consultaToastStack" class="consulta-toast-stack" aria-live="polite" aria-atomic="true"></div>
            @php
                $formatCpf = static function ($value): string {
                    $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                    if ($digits === '') {
                        return '-';
                    }
                    if (strlen($digits) < 11) {
                        return $digits;
                    }
                    $digits = substr($digits, 0, 11);
                    return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
                };

                $rows = is_array($consultaRows ?? null) ? $consultaRows : [];
                $preferredColumns = ['_modulo_consulta', 'nome', 'cliente_nome', 'cpf', 'cliente_cpf', 'nb', 'numero_beneficio', 'status', 'resposta_api', 'created_at'];
                $allColumns = [];
                $seenColumns = [];

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    foreach (array_keys($row) as $column) {
                        if (! isset($seenColumns[$column])) {
                            $seenColumns[$column] = true;
                            $allColumns[] = $column;
                        }
                    }
                }

                $ordered = array_values(array_unique(array_merge(
                    array_values(array_intersect($preferredColumns, $allColumns)),
                    $allColumns
                )));
                $tableColumns = array_slice($ordered, 0, 9);

                $selectedModulesFromRequest = array_values(array_filter(array_map(
                    static fn ($item) => strtolower(trim((string) $item)),
                    (array) ($consultaSelectedModules ?? [])
                )));
                $hasSelectionFromRequest = count($selectedModulesFromRequest) > 0;
                $defaultCheckedKeys = ['macica', 'entrantes'];

                $consultaModules = [
                    ['key' => 'macica', 'label' => 'Maciça'],
                    ['key' => 'entrantes', 'label' => 'Entrantes'],
                    ['key' => 'consulta_in100', 'label' => 'IN100 Qualibanking'],
                    ['key' => 'presenca', 'label' => 'Presença'],
                    ['key' => 'hand_mais', 'label' => 'Hand+'],
                    ['key' => 'prata', 'label' => 'Prata'],
                    ['key' => 'v8', 'label' => 'V8'],
                ];
                $consultaModules = array_map(static function (array $module) use ($hasSelectionFromRequest, $selectedModulesFromRequest, $defaultCheckedKeys): array {
                    $moduleKey = (string) ($module['key'] ?? '');
                    $module['checked'] = $hasSelectionFromRequest
                        ? in_array($moduleKey, $selectedModulesFromRequest, true)
                        : in_array($moduleKey, $defaultCheckedKeys, true);

                    return $module;
                }, $consultaModules);

                $checkedModulesCount = count(array_filter($consultaModules, static fn ($module): bool => !empty($module['checked'])));
                $consultaSelectedModulesCsv = implode(',', array_values(array_map(
                    static fn (array $module): string => (string) $module['key'],
                    array_values(array_filter($consultaModules, static fn (array $module): bool => !empty($module['checked'])))
                )));

                $rowsByModule = is_array($consultaRaw['rows_by_module'] ?? null) ? $consultaRaw['rows_by_module'] : [];
                $macicaRows = array_values(array_filter((array) ($rowsByModule['macica'] ?? []), 'is_array'));
                $entrantesRows = array_values(array_filter((array) ($rowsByModule['entrantes'] ?? []), 'is_array'));
                $primarySources = array_values(array_filter([
                    $macicaRows[0] ?? null,
                    $entrantesRows[0] ?? null,
                    is_array($rows[0] ?? null) ? $rows[0] : null,
                ], 'is_array'));

                $firstValue = static function (array $sources, array $keys, $default = '-') {
                    foreach ($keys as $key) {
                        foreach ($sources as $source) {
                            if (!is_array($source)) {
                                continue;
                            }
                            if (!array_key_exists($key, $source)) {
                                continue;
                            }
                            $value = $source[$key];
                            if ($value === null) {
                                continue;
                            }
                            $text = trim((string) $value);
                            if ($text === '') {
                                continue;
                            }
                            return $text;
                        }
                    }
                    return $default;
                };

                $formatDate = static function ($value): string {
                    $text = trim((string) $value);
                    if ($text === '' || $text === '-') {
                        return '-';
                    }

                    $timestamp = strtotime($text);
                    if ($timestamp === false) {
                        return $text;
                    }

                    return date('d/m/Y', $timestamp);
                };

                $formatDateTime = static function ($value): string {
                    $text = trim((string) $value);
                    if ($text === '' || $text === '-') {
                        return '-';
                    }

                    $timestamp = strtotime($text);
                    if ($timestamp === false) {
                        return $text;
                    }

                    return date('d/m/Y, H:i:s', $timestamp);
                };

                $formatMoney = static function ($value): string {
                    if ($value === null || $value === '') {
                        return '-';
                    }

                    $text = str_replace(',', '.', preg_replace('/[^\d,\.\-]/', '', (string) $value) ?? '');
                    if ($text === '' || !is_numeric($text)) {
                        return trim((string) $value) !== '' ? (string) $value : '-';
                    }

                    return 'R$ '.number_format((float) $text, 2, ',', '.');
                };

                $dedupeRows = static function (array $rows, array $keys): array {
                    $seen = [];
                    $result = [];

                    foreach ($rows as $row) {
                        if (!is_array($row)) {
                            continue;
                        }

                        $parts = [];
                        foreach ($keys as $key) {
                            $parts[] = mb_strtolower(trim((string) ($row[$key] ?? '')));
                        }

                        $signature = implode('|', $parts);
                        if (isset($seen[$signature])) {
                            continue;
                        }

                        $seen[$signature] = true;
                        $result[] = $row;
                    }

                    return $result;
                };

                $nomeCliente = strtoupper((string) $firstValue($primarySources, ['nome_segurado', 'NOME', 'nome', 'cliente_nome']));
                $cpfCliente = (string) $firstValue($primarySources, ['nu_cpf_ix', 'CPF_LIMPO', 'nu_cpf', 'CPF', 'cpf', 'cliente_cpf']);
                $dataNascimento = $formatDate($firstValue($primarySources, ['dt_nascimento', 'Data_Nascimento', 'dt_nascimento_tratado']));
                $idadeCliente = (string) $firstValue($primarySources, ['idade', 'IDADE'], '-');
                $ufCliente = (string) $firstValue($primarySources, ['uf', 'UF'], '-');
                $beneficioCliente = (string) $firstValue($primarySources, ['nb_ix', 'BENEFICIO_LIMPO', 'Beneficio', 'nb', 'numero_beneficio'], '-');
                $dataAtualizacao = $formatDateTime($firstValue($primarySources, ['data_update', 'Data_Lemit', 'created_at'], '-'));

                $margemRmc = $formatMoney($firstValue($primarySources, ['MARGEM_RMC', 'valor_liberador_RMC'], '-'));
                $margemRcc = $formatMoney($firstValue($primarySources, ['Margem_RCC', 'valor_liberador_RCC'], '-'));
                $totalCartao = $formatMoney($firstValue($primarySources, ['total_cartao', 'Total_Valor_Liberado(0.02801)'], '-'));
                $margemLivre = $formatMoney($firstValue($primarySources, ['MARGEM_DISPONIVEL'], '-'));
                $valorConsignavel = $formatMoney($firstValue($primarySources, ['VALOR_BENEFICIO', 'vl_beneficio', 'vl_beneficio_tratado'], '-'));

                $telefones = [];
                foreach ($entrantesRows as $row) {
                    foreach (['CELULAR1', 'CELULAR2', 'CELULAR3', 'CELULAR4'] as $phoneKey) {
                        $digits = preg_replace('/\D+/', '', (string) ($row[$phoneKey] ?? '')) ?? '';
                        if ($digits === '') {
                            continue;
                        }
                        $telefones[$digits] = $digits;
                    }
                }
                $telefones = array_values($telefones);

                $enderecos = [];
                foreach ($macicaRows as $row) {
                    $cep = preg_replace('/\D+/', '', (string) ($row['cep'] ?? '')) ?? '';
                    $rua = trim((string) ($row['endereco'] ?? ''));
                    $bairro = trim((string) ($row['bairro'] ?? ''));
                    $cidade = trim((string) ($row['municipio'] ?? ''));
                    $uf = trim((string) ($row['uf'] ?? ''));

                    if ($cep === '' && $rua === '' && $bairro === '' && $cidade === '' && $uf === '') {
                        continue;
                    }

                    $enderecos[] = [
                        'cep' => $cep,
                        'rua' => $rua,
                        'bairro' => $bairro,
                        'cidade' => trim($cidade.' / '.$uf, ' /'),
                    ];
                }

                if ($enderecos === [] && $entrantesRows !== []) {
                    $firstEntrante = $entrantesRows[0];
                    $cidade = trim((string) ($firstEntrante['Municipio'] ?? ''));
                    $uf = trim((string) ($firstEntrante['UF'] ?? ''));
                    if ($cidade !== '' || $uf !== '') {
                        $enderecos[] = [
                            'cep' => '-',
                            'rua' => '-',
                            'bairro' => '-',
                            'cidade' => trim($cidade.' / '.$uf, ' /'),
                        ];
                    }
                }
                $enderecos = $dedupeRows($enderecos, ['cep', 'rua', 'bairro', 'cidade']);

                $dadosBancarios = [];
                foreach ($macicaRows as $row) {
                    $dadosBancarios[] = [
                        'codigo' => trim((string) ($row['id_banco_pagto'] ?? '-')) ?: '-',
                        'banco' => trim((string) ($row['id_banco_empres'] ?? '-')) ?: '-',
                        'agencia' => trim((string) ($row['id_agencia_banco'] ?? '-')) ?: '-',
                        'conta' => trim((string) ($row['nu_conta_corrente'] ?? '-')) ?: '-',
                        'tipo' => trim((string) ($row['tipo_empres'] ?? $row['cs_meio_pagto'] ?? '-')) ?: '-',
                    ];
                }

                if ($dadosBancarios === [] && $entrantesRows !== []) {
                    foreach ($entrantesRows as $row) {
                        $dadosBancarios[] = [
                            'codigo' => '-',
                            'banco' => trim((string) ($row['Banco'] ?? '-')) ?: '-',
                            'agencia' => trim((string) ($row['Agencia'] ?? '-')) ?: '-',
                            'conta' => trim((string) ($row['Conta'] ?? '-')) ?: '-',
                            'tipo' => trim((string) ($row['Meio_Pagamento'] ?? '-')) ?: '-',
                        ];
                    }
                }
                $dadosBancarios = $dedupeRows($dadosBancarios, ['codigo', 'banco', 'agencia', 'conta', 'tipo']);

                $dadosBancarios = array_slice($dadosBancarios, 0, 8);

                $portabilidadeRows = [];
                foreach ($macicaRows as $row) {
                    $pagas = trim((string) ($row['pagas'] ?? ''));
                    $restantes = trim((string) ($row['restantes'] ?? ''));
                    $portabilidadeRows[] = [
                        'banco' => trim((string) ($row['id_banco_empres'] ?? $row['id_banco_pagto'] ?? '-')) ?: '-',
                        'contrato' => trim((string) ($row['id_contrato_empres'] ?? '-')) ?: '-',
                        'parcelas' => trim($pagas.' / '.$restantes, ' /') !== '' ? trim($pagas.' / '.$restantes, ' /') : '-',
                        'taxa' => '-',
                        'valor_parcela' => $formatMoney($row['vl_parcela'] ?? $row['vl_parcela_tratado'] ?? null),
                        'emprestado' => $formatMoney($row['vl_empres'] ?? $row['vl_empres_tratado'] ?? null),
                        'atualizacao' => $formatDate($row['data_update'] ?? '-'),
                    ];
                }
                $portabilidadeRows = $dedupeRows($portabilidadeRows, ['banco', 'contrato', 'parcelas', 'valor_parcela', 'emprestado', 'atualizacao']);

                $portabilidadeRows = array_slice($portabilidadeRows, 0, 10);

                $modalidadeCards = [
                    ['key' => 'macica', 'label' => 'Portabilidade', 'badge' => 'P'],
                    ['key' => 'consulta_in100', 'label' => 'Qualibanking', 'badge' => 'Q'],
                    ['key' => 'v8', 'label' => 'V8', 'badge' => 'V8'],
                    ['key' => 'presenca', 'label' => 'Presenca', 'badge' => 'PR'],
                    ['key' => 'hand_mais', 'label' => 'Hand+', 'badge' => 'H+'],
                    ['key' => 'prata', 'label' => 'Prata', 'badge' => 'PT'],
                    ['key' => 'entrantes', 'label' => 'Entrantes', 'badge' => 'EN'],
                ];
            @endphp

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
                <div id="consultaClienteLayout" class="consulta-cliente-layout">
                    <section class="consulta-cliente-left p-6">
                        <div class="consulta-top-grid">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Consulta por CPF</h3>
                                <form method="GET" action="{{ route('consultas.cliente') }}" id="consultaCpfForm" class="mt-3">
                                    <label class="block">
                                        <span class="text-sm font-medium text-gray-700">CPF</span>
                                        <div class="mt-1 flex items-center gap-2">
                                            <input
                                                type="text"
                                                id="cpfMaskedInput"
                                                name="cpf_masked"
                                                value="{{ old('cpf', $cpfInput ?? '') }}"
                                                inputmode="numeric"
                                                maxlength="14"
                                                placeholder="000.000.000-00"
                                                autocomplete="off"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <button
                                                type="submit"
                                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                                title="Consultar CPF"
                                                aria-label="Consultar CPF"
                                            >
                                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <circle cx="11" cy="11" r="7"></circle>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                                </svg>
                                            </button>
                                        </div>
                                    </label>
                                    <input type="hidden" name="cpf" id="cpfRawInput" value="{{ preg_replace('/\D+/', '', (string) old('cpf', $cpfInput ?? '')) }}">
                                    <input type="hidden" name="consultas" id="consultasSelecionadasInput" value="{{ $consultaSelectedModulesCsv }}">
                                </form>

                                <p class="mt-2 text-xs text-gray-500">
                                    Tambem funciona direto por URL: <code>?cpf=00000000000&consultas=macica,entrantes</code>
                                </p>

                                @if (!empty($cpfConsulta ?? '') && empty($consultaError))
                                    <div class="mt-4 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                        Consulta para CPF: <span class="font-semibold">{{ $formatCpf($cpfConsulta) }}</span>
                                    </div>
                                @endif

                                <div id="dadosAdicionaisPanel" class="dados-adicionais-panel">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Dados adicionais</h3>
                                    <div id="dadosAdicionaisLista" class="mt-3 space-y-3"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Consultas</h3>
                                    <span id="consultasCounter" class="inline-flex items-center rounded-md border border-gray-300 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $checkedModulesCount }}/{{ count($consultaModules) }}</span>
                                </div>
                                <div id="consultasDropdown" class="consultas-dropdown mt-3">
                                    <button type="button" id="consultasDropdownTrigger" class="consultas-dropdown__trigger" aria-expanded="false" aria-haspopup="listbox">
                                        <span id="consultasDropdownLabel">Selecione consultas</span>
                                        <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="4 7 10 13 16 7"></polyline>
                                        </svg>
                                    </button>

                                    <div id="consultasDropdownMenu" class="consultas-dropdown__menu" role="listbox" aria-multiselectable="true">
                                        <input type="text" id="consultasDropdownSearch" class="consultas-dropdown__search" placeholder="Buscar consulta...">
                                        <div class="consultas-dropdown__actions">
                                            <button type="button" id="consultasDropdownSelectAll" class="consultas-dropdown__action">Selecionar todos</button>
                                            <button type="button" id="consultasDropdownClearAll" class="consultas-dropdown__action">Limpar todos</button>
                                        </div>
                                        <div id="consultasDropdownList" class="consultas-dropdown__list"></div>
                                    </div>
                                </div>

                                <div class="hidden">
                                    @foreach ($consultaModules as $module)
                                        <input
                                            type="checkbox"
                                            class="js-consulta-checkbox h-4 w-4 rounded border-gray-300"
                                            data-module-key="{{ $module['key'] }}"
                                            data-module-label="{{ $module['label'] }}"
                                            @checked(!empty($module['checked']))
                                        >
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @if (count($rows) > 0)
                            <div class="consulta-result-stack">
                                <section class="consulta-result-card">
                                    <div class="consulta-result-card__header">
                                        <h4 class="consulta-result-title">Dados pessoais</h4>
                                        <span class="text-xs font-semibold text-gray-500">Atualizado: {{ $dataAtualizacao }}</span>
                                    </div>
                                    <div class="consulta-result-grid-6">
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">Nome</span>
                                            <span class="consulta-result-field__value">{{ $nomeCliente !== '' ? $nomeCliente : '-' }}</span>
                                        </div>
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">CPF</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="consulta-result-field__value">{{ $formatCpf($cpfCliente) }}</span>
                                                <button type="button" class="consulta-copy-btn" data-copy-text="{{ preg_replace('/\D+/', '', $cpfCliente) }}" title="Copiar CPF" aria-label="Copiar CPF">
                                                    ⧉
                                                </button>
                                            </div>
                                        </div>
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">Data nascimento</span>
                                            <span class="consulta-result-field__value">{{ $dataNascimento }}</span>
                                        </div>
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">Idade</span>
                                            <span class="consulta-result-field__value">{{ is_numeric($idadeCliente) ? $idadeCliente.' anos' : $idadeCliente }}</span>
                                        </div>
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">UF</span>
                                            <span class="consulta-result-field__value">{{ $ufCliente !== '' ? $ufCliente : '-' }}</span>
                                        </div>
                                        <div class="consulta-result-field">
                                            <span class="consulta-result-field__label">Beneficio (NB)</span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="consulta-result-field__value">{{ $beneficioCliente }}</span>
                                                <button type="button" class="consulta-copy-btn" data-copy-text="{{ $beneficioCliente }}" title="Copiar beneficio" aria-label="Copiar beneficio">
                                                    ⧉
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="consulta-result-grid-5">
                                    <article class="consulta-kpi">
                                        <div class="consulta-kpi__label">Margem RMC</div>
                                        <div class="consulta-kpi__value">{{ $margemRmc }}</div>
                                    </article>
                                    <article class="consulta-kpi">
                                        <div class="consulta-kpi__label">Margem RCC</div>
                                        <div class="consulta-kpi__value">{{ $margemRcc }}</div>
                                    </article>
                                    <article class="consulta-kpi">
                                        <div class="consulta-kpi__label">Total cartao</div>
                                        <div class="consulta-kpi__value">{{ $totalCartao }}</div>
                                    </article>
                                    <article class="consulta-kpi">
                                        <div class="consulta-kpi__label">Margem livre</div>
                                        <div class="consulta-kpi__value">{{ $margemLivre }}</div>
                                    </article>
                                    <article class="consulta-kpi">
                                        <div class="consulta-kpi__label">Valor consignavel</div>
                                        <div class="consulta-kpi__value">{{ $valorConsignavel }}</div>
                                    </article>
                                </section>

                                <section class="consulta-result-grid-2">
                                    <article class="consulta-result-card">
                                        <div class="consulta-result-card__header">
                                            <h4 class="consulta-result-title">Informacoes da matricula</h4>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-md bg-slate-700 px-2 py-1 text-xs font-bold text-white">Matricula {{ $beneficioCliente }}</span>
                                                <button type="button" class="consulta-copy-btn" data-copy-text="{{ $beneficioCliente }}" title="Copiar matricula" aria-label="Copiar matricula">⧉</button>
                                            </div>
                                        </div>
                                        <div class="overflow-x-auto px-3 pb-3">
                                            <table class="consulta-list-table">
                                                <tbody>
                                                    <tr>
                                                        <th>NB</th>
                                                        <td>{{ $beneficioCliente }}</td>
                                                        <th>Consignavel</th>
                                                        <td>{{ $valorConsignavel }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Especie</th>
                                                        <td>{{ $firstValue($primarySources, ['esp', 'CODIGO_ESPECIE']) }}</td>
                                                        <th>Situacao</th>
                                                        <td>{{ $firstValue($primarySources, ['situacao_empres']) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Data inicio beneficio</th>
                                                        <td>{{ $formatDate($firstValue($primarySources, ['dib'])) }}</td>
                                                        <th>UF</th>
                                                        <td>{{ $ufCliente }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Data despacho beneficio</th>
                                                        <td>{{ $formatDate($firstValue($primarySources, ['ddb', 'DDB'])) }}</td>
                                                        <th></th>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </article>

                                    <article class="consulta-result-card">
                                        <div class="consulta-result-card__header">
                                            <h4 class="consulta-result-title">Telefones ({{ count($telefones) }})</h4>
                                        </div>
                                        <div class="overflow-x-auto px-3 pb-3">
                                            @if ($telefones !== [])
                                                <table class="consulta-list-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Numero</th>
                                                            <th>Inclusao</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($telefones as $phone)
                                                            <tr>
                                                                <td>{{ $phone }}</td>
                                                                <td>-</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="consulta-empty">Nenhum telefone disponivel.</div>
                                            @endif
                                        </div>
                                    </article>
                                </section>

                                <section class="consulta-result-card">
                                    <div class="consulta-result-card__header">
                                        <h4 class="consulta-result-title">Enderecos ({{ count($enderecos) }})</h4>
                                    </div>
                                    <div class="overflow-x-auto px-3 pb-3">
                                        @if ($enderecos !== [])
                                            <table class="consulta-list-table">
                                                <thead>
                                                    <tr>
                                                        <th>CEP</th>
                                                        <th>Rua</th>
                                                        <th>Bairro</th>
                                                        <th>Cidade</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($enderecos as $endereco)
                                                        <tr>
                                                            <td>{{ $endereco['cep'] !== '' ? $endereco['cep'] : '-' }}</td>
                                                            <td>{{ $endereco['rua'] !== '' ? $endereco['rua'] : '-' }}</td>
                                                            <td>{{ $endereco['bairro'] !== '' ? $endereco['bairro'] : '-' }}</td>
                                                            <td>{{ $endereco['cidade'] !== '' ? $endereco['cidade'] : '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="consulta-empty">Nenhum endereco disponivel.</div>
                                        @endif
                                    </div>
                                </section>

                                <section class="consulta-result-grid-2-wide">
                                    <article class="consulta-result-card">
                                        <div class="consulta-result-card__header">
                                            <h4 class="consulta-result-title">Dados bancarios ({{ count($dadosBancarios) }})</h4>
                                        </div>
                                        <div class="overflow-x-auto px-3 pb-3">
                                            @if ($dadosBancarios !== [])
                                                <table class="consulta-list-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Cod.</th>
                                                            <th>Banco</th>
                                                            <th>Agencia</th>
                                                            <th>Conta</th>
                                                            <th>Tipo de credito</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($dadosBancarios as $banco)
                                                            <tr>
                                                                <td>{{ $banco['codigo'] }}</td>
                                                                <td>{{ $banco['banco'] }}</td>
                                                                <td>{{ $banco['agencia'] }}</td>
                                                                <td>{{ $banco['conta'] }}</td>
                                                                <td>{{ $banco['tipo'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="consulta-empty">Nenhum dado bancario disponivel.</div>
                                            @endif
                                        </div>
                                    </article>

                                    <article class="consulta-result-card">
                                        <div class="consulta-result-card__header">
                                            <h4 class="consulta-result-title">Modalidades de contrato</h4>
                                        </div>
                                        <div>
                                            @foreach ($modalidadeCards as $mod)
                                                @php
                                                    $count = is_array($rowsByModule[$mod['key']] ?? null) ? count($rowsByModule[$mod['key']]) : 0;
                                                @endphp
                                                <div class="consulta-module-row">
                                                    <span class="consulta-module-row__name">
                                                        <span class="consulta-chip">{{ $mod['badge'] }}</span>
                                                        {{ $mod['label'] }}
                                                    </span>
                                                    <span class="consulta-module-row__count">{{ $count }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </article>
                                </section>

                                <section class="consulta-result-card">
                                    <div class="consulta-result-card__header">
                                        <h4 class="consulta-result-title">Portabilidade</h4>
                                    </div>
                                    <div class="overflow-x-auto px-3 pb-3">
                                        @if ($portabilidadeRows !== [])
                                            <table class="consulta-list-table">
                                                <thead>
                                                    <tr>
                                                        <th>Banco</th>
                                                        <th>N do contrato</th>
                                                        <th>Pago/Restantes (Parcelas)</th>
                                                        <th>Taxa</th>
                                                        <th>Valor parcela</th>
                                                        <th>Emprestado</th>
                                                        <th>Ultima atualizacao</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($portabilidadeRows as $port)
                                                        <tr>
                                                            <td>{{ $port['banco'] }}</td>
                                                            <td>{{ $port['contrato'] }}</td>
                                                            <td>{{ $port['parcelas'] }}</td>
                                                            <td>{{ $port['taxa'] }}</td>
                                                            <td>{{ $port['valor_parcela'] }}</td>
                                                            <td>{{ $port['emprestado'] }}</td>
                                                            <td>{{ $port['atualizacao'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="consulta-empty">Sem contratos de portabilidade para este CPF.</div>
                                        @endif
                                    </div>
                                </section>
                            </div>
                        @endif

                        @if (!empty($consultaRaw) && config('app.debug'))
                            <details class="mt-4">
                                <summary class="cursor-pointer text-sm font-medium text-gray-700">Ver retorno bruto</summary>
                                <pre class="mt-2 rounded-md bg-gray-900 p-3 text-xs text-gray-100 overflow-auto">{{ json_encode($consultaRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @endif
                    </section>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const layout = document.getElementById('consultaClienteLayout');
            if (!layout) return;

            const checkboxes = Array.from(layout.querySelectorAll('.js-consulta-checkbox'));
            const counter = document.getElementById('consultasCounter');
            const panel = document.getElementById('dadosAdicionaisPanel');
            const list = document.getElementById('dadosAdicionaisLista');
            const selectedModulesInput = document.getElementById('consultasSelecionadasInput');
            const consultasDropdown = document.getElementById('consultasDropdown');
            const consultasDropdownTrigger = document.getElementById('consultasDropdownTrigger');
            const consultasDropdownMenu = document.getElementById('consultasDropdownMenu');
            const consultasDropdownList = document.getElementById('consultasDropdownList');
            const consultasDropdownSearch = document.getElementById('consultasDropdownSearch');
            const consultasDropdownSelectAll = document.getElementById('consultasDropdownSelectAll');
            const consultasDropdownClearAll = document.getElementById('consultasDropdownClearAll');
            const consultasDropdownLabel = document.getElementById('consultasDropdownLabel');
            const toastStack = document.getElementById('consultaToastStack');
            const cpfForm = document.getElementById('consultaCpfForm');
            const cpfMaskedInput = document.getElementById('cpfMaskedInput');
            const cpfRawInput = document.getElementById('cpfRawInput');
            const modulesWithExtraData = new Set(['consulta_in100', 'presenca', 'hand_mais', 'prata', 'v8']);
            const moduleSelectionStorageKey = 'europa45.consultaCliente.moduleSelection';
            const initialErrorMessage = @json((string) ($consultaError ?? ''));
            const shouldShowNoResultToast = @json(!empty($cpfConsulta ?? '') && empty($consultaError ?? '') && count($rows) === 0);
            const noResultMessage = 'Nenhum dado encontrado para este CPF.';
            let wasPanelOpen = false;
            let previousExtraDataSignature = '';
            let fanTimer = null;
            let nextToastId = 1;
            const toastTimers = {};
            let dropdownIsOpen = false;
            const additionalFieldMap = {
                consulta_in100: [
                    { key: 'beneficio', label: 'Benefício', type: 'text', required: true, placeholder: 'Digite o benefício' },
                ],
                presenca: [
                    { key: 'nome_completo', label: 'Nome completo', type: 'text', required: true, uppercase: true, placeholder: 'NOME COMPLETO' },
                    { key: 'telefone', label: 'Telefone (opcional)', type: 'text', required: false, phoneAuto: true, placeholder: 'Ex.: 11999999999' },
                ],
                hand_mais: [
                    { key: 'nome_completo', label: 'Nome completo', type: 'text', required: true, placeholder: 'Digite o nome completo' },
                    { key: 'data_nascimento', label: 'Data de nascimento', type: 'date', required: true },
                    { key: 'telefone', label: 'Telefone (opcional)', type: 'text', required: false, phoneAuto: true, placeholder: 'Ex.: 11999999999' },
                ],
                prata: [
                    { key: 'nome_completo', label: 'Nome completo', type: 'text', required: true, placeholder: 'Digite o nome completo' },
                ],
                v8: [
                    { key: 'nome_completo', label: 'Nome completo', type: 'text', required: true, placeholder: 'Digite o nome completo' },
                ],
            };
            const additionalState = {};

            const generatePhoneNumber = function () {
                const min = 11911111111n;
                const max = 99999999999n;

                while (true) {
                    const firstDigit = Math.floor(Math.random() * 9) + 1;
                    const secondDigit = Math.floor(Math.random() * 10);
                    let tail = '';
                    for (let i = 0; i < 8; i += 1) {
                        tail += Math.floor(Math.random() * 10).toString();
                    }

                    const candidate = `${firstDigit}${secondDigit}9${tail}`;
                    const candidateNumber = BigInt(candidate);

                    if (candidateNumber >= min && candidateNumber <= max) {
                        return candidate;
                    }
                }
            };

            const toastIcon = function (type) {
                if (type === 'success') return '+';
                if (type === 'error') return '!';
                return 'i';
            };

            const normalizeLabel = function (text) {
                return String(text || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            };

            const setDropdownOpen = function (open) {
                if (!consultasDropdown || !consultasDropdownTrigger || !consultasDropdownMenu) return;
                dropdownIsOpen = !!open;
                consultasDropdown.classList.toggle('is-open', dropdownIsOpen);
                consultasDropdownTrigger.setAttribute('aria-expanded', dropdownIsOpen ? 'true' : 'false');
                consultasDropdownMenu.setAttribute('aria-hidden', dropdownIsOpen ? 'false' : 'true');

                if (dropdownIsOpen && consultasDropdownSearch) {
                    setTimeout(function () {
                        consultasDropdownSearch.focus();
                    }, 0);
                }
            };

            const getCheckedModules = function () {
                return checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                });
            };

            const getDropdownFilterValue = function () {
                return normalizeLabel(consultasDropdownSearch ? consultasDropdownSearch.value : '');
            };

            const renderDropdownOptions = function () {
                if (!consultasDropdownList) return;

                const filter = getDropdownFilterValue();
                const modules = checkboxes
                    .map(function (checkbox) {
                        return {
                            key: checkbox.dataset.moduleKey || '',
                            label: checkbox.dataset.moduleLabel || checkbox.dataset.moduleKey || '',
                            checked: !!checkbox.checked,
                        };
                    })
                    .filter(function (module) {
                        return module.key !== '';
                    })
                    .filter(function (module) {
                        if (filter === '') return true;
                        return normalizeLabel(module.label).includes(filter);
                    });

                consultasDropdownList.innerHTML = '';

                if (modules.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'consultas-dropdown__empty';
                    empty.textContent = 'Nenhuma consulta encontrada.';
                    consultasDropdownList.appendChild(empty);
                    return;
                }

                modules.forEach(function (module) {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = `consultas-dropdown__option${module.checked ? ' is-selected' : ''}`;
                    option.dataset.moduleKey = module.key;
                    option.dataset.moduleLabel = module.label;

                    const text = document.createElement('span');
                    text.textContent = module.label;
                    option.appendChild(text);

                    const check = document.createElement('span');
                    check.className = 'consultas-dropdown__check';
                    check.textContent = '✓';
                    option.appendChild(check);

                    option.addEventListener('click', function () {
                        const checkbox = checkboxes.find(function (item) {
                            return (item.dataset.moduleKey || '') === module.key;
                        });
                        if (!checkbox) return;

                        checkbox.checked = !checkbox.checked;
                        refreshUi();
                    });

                    consultasDropdownList.appendChild(option);
                });
            };

            const updateDropdownLabel = function (checkedModules) {
                if (!consultasDropdownLabel) return;

                if (checkedModules.length === 0) {
                    consultasDropdownLabel.textContent = 'Selecione consultas';
                    return;
                }

                if (checkedModules.length === 1) {
                    consultasDropdownLabel.textContent = checkedModules[0].dataset.moduleLabel || checkedModules[0].dataset.moduleKey || '1 selecionada';
                    return;
                }

                if (checkedModules.length === 2) {
                    const labels = checkedModules.map(function (item) {
                        return item.dataset.moduleLabel || item.dataset.moduleKey || '';
                    }).filter(Boolean);
                    consultasDropdownLabel.textContent = labels.join(', ');
                    return;
                }

                consultasDropdownLabel.textContent = `${checkedModules.length} selecionadas`;
            };

            const dismissToast = function (id) {
                const toastElement = toastStack ? toastStack.querySelector(`[data-toast-id="${id}"]`) : null;
                if (toastElement && toastElement.parentNode) {
                    toastElement.parentNode.removeChild(toastElement);
                }

                if (toastTimers[id]) {
                    clearTimeout(toastTimers[id]);
                    delete toastTimers[id];
                }
            };

            const pushToast = function (type, message, duration = 4200) {
                if (!toastStack || !message) return;

                const id = nextToastId++;
                const toast = document.createElement('div');
                toast.dataset.toastId = String(id);
                toast.className = `consulta-toast consulta-toast--${type}`;

                const icon = document.createElement('span');
                icon.className = 'consulta-toast__icon';
                icon.textContent = toastIcon(type);
                toast.appendChild(icon);

                const text = document.createElement('div');
                text.className = 'consulta-toast__text';
                text.textContent = message;
                toast.appendChild(text);

                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'consulta-toast__close';
                close.setAttribute('aria-label', 'Fechar');
                close.textContent = 'x';
                close.addEventListener('click', function () {
                    dismissToast(id);
                });
                toast.appendChild(close);

                toastStack.appendChild(toast);

                if (duration > 0) {
                    toastTimers[id] = setTimeout(function () {
                        dismissToast(id);
                    }, duration);
                }
            };

            const digitsOnly = function (value) {
                return String(value || '').replace(/\D+/g, '');
            };

            const formatCpfMasked = function (digits) {
                const raw = digitsOnly(digits).slice(0, 11);
                if (raw.length <= 3) return raw;
                if (raw.length <= 6) return `${raw.slice(0, 3)}.${raw.slice(3)}`;
                if (raw.length <= 9) return `${raw.slice(0, 3)}.${raw.slice(3, 6)}.${raw.slice(6)}`;
                return `${raw.slice(0, 3)}.${raw.slice(3, 6)}.${raw.slice(6, 9)}-${raw.slice(9, 11)}`;
            };

            const syncCpfFromMaskedInput = function () {
                if (!cpfMaskedInput || !cpfRawInput) return;
                const raw = digitsOnly(cpfMaskedInput.value).slice(0, 11);
                cpfRawInput.value = raw;
                cpfMaskedInput.value = formatCpfMasked(raw);
            };

            const initCpfInput = function () {
                if (!cpfMaskedInput || !cpfRawInput) return;

                const initialRaw = digitsOnly(cpfRawInput.value || cpfMaskedInput.value).slice(0, 11);
                cpfRawInput.value = initialRaw;
                cpfMaskedInput.value = initialRaw ? formatCpfMasked(initialRaw.padStart(11, '0')) : '';

                cpfMaskedInput.addEventListener('input', syncCpfFromMaskedInput);

                if (cpfForm) {
                    cpfForm.addEventListener('submit', function (event) {
                        const raw = digitsOnly(cpfMaskedInput.value).slice(0, 11);
                        cpfRawInput.value = raw;
                        const selectedModulesCsv = String(selectedModulesInput?.value || '').trim();

                        if (selectedModulesCsv === '') {
                            event.preventDefault();
                            pushToast('error', 'Selecione pelo menos uma consulta antes de consultar.', 4200);
                            return;
                        }

                        if (raw === '') {
                            event.preventDefault();
                            pushToast('error', 'Informe um CPF para consultar.', 4200);
                            return;
                        }

                        cpfMaskedInput.value = formatCpfMasked(raw.padStart(11, '0'));
                    });
                }
            };

            const setStateValue = function (fieldKey, value) {
                additionalState[fieldKey] = value;
            };

            const triggerPanelFan = function () {
                if (!panel) return;

                panel.classList.remove('fan-animate');
                void panel.offsetWidth;
                panel.classList.add('fan-animate');

                if (fanTimer) {
                    clearTimeout(fanTimer);
                }

                fanTimer = setTimeout(function () {
                    panel.classList.remove('fan-animate');
                    fanTimer = null;
                }, 320);
            };

            const getStateValue = function (fieldKey) {
                return additionalState[fieldKey] ?? '';
            };

            const ensureAutoPhone = function (fieldKey, input) {
                if ((input.value || '').trim() !== '') {
                    return;
                }

                const generated = generatePhoneNumber();
                input.value = generated;
                setStateValue(fieldKey, generated);
            };

            const getMergedFields = function (modules) {
                const merged = new Map();

                modules.forEach(function (module) {
                    const moduleFields = additionalFieldMap[module.key] || [];

                    moduleFields.forEach(function (field) {
                        if (!merged.has(field.key)) {
                            merged.set(field.key, {
                                key: field.key,
                                label: field.label,
                                type: field.type || 'text',
                                required: !!field.required,
                                uppercase: !!field.uppercase,
                                phoneAuto: !!field.phoneAuto,
                                placeholder: field.placeholder || '',
                            });
                            return;
                        }

                        const current = merged.get(field.key);
                        current.required = current.required || !!field.required;
                        current.uppercase = current.uppercase || !!field.uppercase;
                        current.phoneAuto = current.phoneAuto || !!field.phoneAuto;
                        if (!current.placeholder && field.placeholder) {
                            current.placeholder = field.placeholder;
                        }
                    });
                });

                return Array.from(merged.values());
            };

            const renderAdditionalDataList = function (modules) {
                if (!list) return;
                list.innerHTML = '';

                if (modules.length === 0) {
                    const item = document.createElement('div');
                    item.className = 'rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-600';
                    item.textContent = 'Selecione IN100 Qualibanking, Presença, Hand+, Prata ou V8.';
                    list.appendChild(item);
                    return;
                }

                const mergedFields = getMergedFields(modules);
                const card = document.createElement('div');
                card.className = 'rounded-md border border-gray-200 p-3';

                const fieldsWrap = document.createElement('div');
                fieldsWrap.className = 'space-y-2';

                mergedFields.forEach(function (field) {
                    const fieldWrap = document.createElement('label');
                    fieldWrap.className = 'block';

                    const fieldLabel = document.createElement('span');
                    fieldLabel.className = 'text-xs font-medium text-gray-700';
                    fieldLabel.textContent = field.label;
                    fieldWrap.appendChild(fieldLabel);

                    const input = document.createElement('input');
                    input.type = field.type || 'text';
                    input.className = 'mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
                    input.placeholder = field.placeholder || '';
                    input.value = getStateValue(field.key);
                    input.required = !!field.required;

                    if (field.phoneAuto) {
                        input.inputMode = 'numeric';
                        input.maxLength = 11;
                    }

                    input.addEventListener('input', function () {
                        if (field.uppercase) {
                            input.value = input.value.toUpperCase();
                        }

                        if (field.phoneAuto) {
                            input.value = input.value.replace(/\D+/g, '').slice(0, 11);
                        }

                        setStateValue(field.key, input.value);
                    });

                    if (field.phoneAuto) {
                        input.addEventListener('blur', function () {
                            ensureAutoPhone(field.key, input);
                        });

                        if ((input.value || '').trim() === '') {
                            ensureAutoPhone(field.key, input);
                        }
                    }

                    fieldWrap.appendChild(input);
                    fieldsWrap.appendChild(fieldWrap);
                });

                card.appendChild(fieldsWrap);
                list.appendChild(card);
            };

            const refreshUi = function () {
                const checked = getCheckedModules();
                const selectedModuleKeys = checked
                    .map(function (checkbox) {
                        return checkbox.dataset.moduleKey || '';
                    })
                    .filter(Boolean);

                try {
                    localStorage.setItem(moduleSelectionStorageKey, JSON.stringify(selectedModuleKeys));
                } catch (error) {
                    // Ignora falhas de storage no navegador.
                }

                if (selectedModulesInput) {
                    selectedModulesInput.value = selectedModuleKeys.join(',');
                }

                if (counter) {
                    counter.textContent = checked.length + '/' + checkboxes.length;
                }

                updateDropdownLabel(checked);
                renderDropdownOptions();

                const selectedWithExtraData = checked
                    .filter(function (checkbox) {
                        return modulesWithExtraData.has(checkbox.dataset.moduleKey || '');
                    })
                    .map(function (checkbox) {
                        return {
                            key: checkbox.dataset.moduleKey || '',
                            label: checkbox.dataset.moduleLabel || checkbox.dataset.moduleKey || '',
                        };
                    })
                    .filter(function (module) {
                        return module.key !== '';
                    });

                const shouldShowPanel = selectedWithExtraData.length > 0;
                const extraDataSignature = selectedWithExtraData.map(function (module) {
                    return module.key;
                }).join('|');
                const shouldAnimatePanel = shouldShowPanel && (!wasPanelOpen || extraDataSignature !== previousExtraDataSignature);

                layout.classList.toggle('has-dados-adicionais', shouldShowPanel);

                if (panel) {
                    panel.style.display = shouldShowPanel ? '' : 'none';

                    if (shouldAnimatePanel) {
                        triggerPanelFan();
                    } else if (!shouldShowPanel) {
                        panel.classList.remove('fan-animate');
                    }
                }

                renderAdditionalDataList(selectedWithExtraData);
                wasPanelOpen = shouldShowPanel;
                previousExtraDataSignature = extraDataSignature;
            };

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', refreshUi);
            });

            if (consultasDropdownTrigger) {
                consultasDropdownTrigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    setDropdownOpen(!dropdownIsOpen);
                });
            }

            if (consultasDropdownSearch) {
                consultasDropdownSearch.addEventListener('input', function () {
                    renderDropdownOptions();
                });
            }

            if (consultasDropdownSelectAll) {
                consultasDropdownSelectAll.addEventListener('click', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = true;
                    });
                    refreshUi();
                });
            }

            if (consultasDropdownClearAll) {
                consultasDropdownClearAll.addEventListener('click', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                    });
                    refreshUi();
                });
            }

            document.addEventListener('click', function (event) {
                if (!consultasDropdown || !dropdownIsOpen) return;
                if (consultasDropdown.contains(event.target)) return;
                setDropdownOpen(false);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && dropdownIsOpen) {
                    setDropdownOpen(false);
                }
            });

            layout.querySelectorAll('[data-copy-text]').forEach(function (button) {
                button.addEventListener('click', async function () {
                    const text = String(button.getAttribute('data-copy-text') || '').trim();
                    if (!text || text === '-') {
                        pushToast('info', 'Nao ha valor para copiar.', 2200);
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(text);
                        pushToast('success', 'Copiado para a area de transferencia.', 1800);
                    } catch (error) {
                        pushToast('error', 'Nao foi possivel copiar este valor.', 2800);
                    }
                });
            });

            try {
                const savedRaw = localStorage.getItem(moduleSelectionStorageKey);
                if (savedRaw) {
                    const savedKeys = JSON.parse(savedRaw);
                    if (Array.isArray(savedKeys)) {
                        const savedSet = new Set(savedKeys.map(function (key) {
                            return String(key || '');
                        }));

                        checkboxes.forEach(function (checkbox) {
                            const key = String(checkbox.dataset.moduleKey || '');
                            checkbox.checked = savedSet.has(key);
                        });
                    }
                }
            } catch (error) {
                // Ignora payload inválido e segue com padrão do servidor.
            }

            initCpfInput();
            refreshUi();
            setDropdownOpen(false);

            if (initialErrorMessage) {
                pushToast('error', initialErrorMessage, 5200);
            } else if (shouldShowNoResultToast) {
                pushToast('info', noResultMessage, 4200);
            }
        });
    </script>
</x-app-layout>
