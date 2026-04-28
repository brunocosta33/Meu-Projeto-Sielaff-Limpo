<style>
    .technical-request-card {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #dfe8e3 !important;
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1) !important;
    }

    .technical-request-card::before {
        content: none;
    }

    .technical-request-card .card-body {
        padding: 0;
    }

    .technical-request-header {
        padding: 1.35rem 1.5rem;
        color: #ffffff;
        background:
            linear-gradient(135deg, rgba(18, 52, 59, 0.98), rgba(23, 116, 91, 0.94)),
            #12343b;
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
    }

    .technical-request-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .technical-request-title-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #12343b;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
    }

    .technical-request-card .card-title {
        color: #ffffff;
        font-weight: 800;
    }

    .technical-request-card .card-title i {
        color: inherit;
    }

    .technical-request-card .text-muted {
        color: #6d7b75 !important;
    }

    .technical-request-header .text-muted {
        color: rgba(255, 255, 255, 0.74) !important;
    }

    .technical-request-header .btn-outline-secondary {
        border-color: rgba(255, 255, 255, 0.55);
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(6px);
    }

    .technical-request-header .btn-outline-secondary:hover {
        color: #12343b;
        background: #ffffff;
        border-color: #ffffff;
    }

    .technical-request-body {
        padding: 1.5rem;
        background:
            linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(13, 110, 253, 0.08) 48%, rgba(111, 66, 193, 0.1)),
            #f6faf8;
    }

    .technical-request-note {
        background: linear-gradient(135deg, #fffaf0, #ffffff);
        border: 1px solid #dbe7e1 !important;
        border-left: 5px solid #f59f00 !important;
        border-radius: 8px;
        color: #43544c;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.045);
    }

    .technical-request-section {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #dde8e3 !important;
        border-left-width: 6px !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.07);
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }

    .technical-request-section:hover {
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.075);
        transform: translateY(-1px);
    }

    .technical-request-section::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 4px;
    }

    .technical-request-section--identity {
        background: linear-gradient(180deg, #effcf5, #ffffff 42%);
        border-color: rgba(25, 135, 84, 0.24) !important;
        border-left-color: #198754 !important;
    }

    .technical-request-section--management {
        background: linear-gradient(180deg, #eef6ff, #ffffff 42%);
        border-color: rgba(13, 110, 253, 0.24) !important;
        border-left-color: #0d6efd !important;
    }

    .technical-request-section--details {
        background: linear-gradient(180deg, #f5f1ff, #ffffff 42%);
        border-color: rgba(111, 66, 193, 0.24) !important;
        border-left-color: #6f42c1 !important;
    }

    .technical-request-section--identity::before {
        background: #198754;
    }

    .technical-request-section--management::before {
        background: #0d6efd;
    }

    .technical-request-section--details::before {
        background: #6f42c1;
    }

    .technical-request-section h6 {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: -1rem -1rem 1.15rem -1rem;
        padding: 0.95rem 1rem 0.85rem 1rem;
        color: #1f3029 !important;
        border-bottom: 1px solid #e3ebe7;
        font-weight: 700;
        letter-spacing: 0;
    }

    .technical-request-section h6 i {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #ffffff !important;
    }

    .technical-request-section--identity h6 i {
        background: #198754;
    }

    .technical-request-section--identity h6 {
        background: linear-gradient(90deg, rgba(25, 135, 84, 0.16), rgba(25, 135, 84, 0.04));
        border-bottom-color: rgba(25, 135, 84, 0.18);
        color: #14532d !important;
    }

    .technical-request-section--management h6 i {
        background: #0d6efd;
    }

    .technical-request-section--management h6 {
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.15), rgba(13, 110, 253, 0.04));
        border-bottom-color: rgba(13, 110, 253, 0.18);
        color: #1d4ed8 !important;
    }

    .technical-request-section--details h6 i {
        background: #6f42c1;
    }

    .technical-request-section--details h6 {
        background: linear-gradient(90deg, rgba(111, 66, 193, 0.15), rgba(111, 66, 193, 0.04));
        border-bottom-color: rgba(111, 66, 193, 0.18);
        color: #5b21b6 !important;
    }

    .technical-request-card label {
        color: #2f4038;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .technical-request-section .form-control,
    .technical-request-section .bootstrap-select > .dropdown-toggle {
        min-height: 42px;
        background-color: #ffffff;
        border-color: #cedbd5;
        border-radius: 8px;
        transition: border-color 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
    }

    .technical-request-section textarea.form-control {
        min-height: auto;
        line-height: 1.5;
    }

    .technical-request-section small.text-muted {
        color: #708179 !important;
    }

    .technical-request-section #store_summary {
        background: #ffffff;
        border-color: #dbe6e1 !important;
        border-radius: 8px;
    }

    .technical-request-card .form-control:focus,
    .technical-request-card .bootstrap-select > .dropdown-toggle:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.14rem rgba(25, 135, 84, 0.15);
    }

    .technical-request-card .btn-success {
        border-color: #198754;
        background: #198754;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(25, 135, 84, 0.22);
    }

    .technical-request-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin: 1.5rem -1.5rem -1.5rem -1.5rem;
        padding: 1rem 1.5rem;
        background:
            linear-gradient(90deg, rgba(25, 135, 84, 0.08), rgba(13, 110, 253, 0.06), rgba(111, 66, 193, 0.08)),
            #ffffff;
        border-top: 1px solid #e0e9e4;
        border-left: 5px solid #198754;
    }

    .technical-request-actions .btn {
        margin-bottom: 0 !important;
    }

    .technical-request-actions .btn-outline-secondary {
        color: #34463e;
        background: #ffffff;
        border-color: #ccd9d3;
        font-weight: 700;
    }

    .technical-request-actions .btn-outline-secondary:hover {
        color: #17261f;
        background: #f5f8f6;
        border-color: #aebeb6;
    }

    .technical-request-files-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 10px;
        margin-top: 0.85rem;
    }

    .technical-request-file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-width: 0;
        padding: 0.7rem;
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid #dfe8e3;
        border-radius: 8px;
    }

    .technical-request-file-info {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .technical-request-file-thumb {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        object-fit: cover;
        border-radius: 7px;
        border: 1px solid #dfe8e3;
    }

    .technical-request-file-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        color: #b42318;
        background: #fff1f0;
        border: 1px solid #ffd6d2;
    }

    .technical-request-file-name {
        min-width: 0;
        color: #26342f;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .technical-request-file-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
    }

    @media (max-width: 767.98px) {
        .technical-request-card {
            border-radius: 8px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08) !important;
        }

        .technical-request-header {
            padding: 0.65rem;
        }

        .technical-request-body {
            padding: 0.55rem;
        }

        .technical-request-title-wrap {
            align-items: flex-start;
            gap: 0.5rem;
        }

        .technical-request-title-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            border-radius: 7px;
            font-size: 0.85rem;
        }

        .technical-request-card .card-title {
            font-size: 0.92rem;
            line-height: 1.2;
            margin-bottom: 0.15rem !important;
        }

        .technical-request-header .text-muted {
            font-size: 0.68rem;
            line-height: 1.25;
        }

        .technical-request-header .mt-3 {
            width: 100%;
            margin-top: 0.55rem !important;
        }

        .technical-request-header .btn {
            width: 100%;
            padding: 0.32rem 0.42rem;
            border-radius: 7px;
            font-size: 0.66rem;
            line-height: 1.1;
            white-space: normal;
        }

        .technical-request-note {
            margin-bottom: 0.55rem;
            padding: 0.45rem !important;
            border-left-width: 4px !important;
            border-radius: 7px;
            font-size: 0.66rem;
            line-height: 1.25;
            box-shadow: none;
        }

        .technical-request-body > .row {
            margin-right: -4px;
            margin-left: -4px;
        }

        .technical-request-body > .row > [class*="col-"] {
            padding-right: 4px;
            padding-left: 4px;
        }

        .technical-request-section {
            border-left-width: 4px !important;
            border-radius: 8px !important;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        }

        .technical-request-section:hover {
            transform: none;
        }

        .technical-request-section h6 {
            gap: 0.35rem;
            margin: -1rem -1rem 0.55rem -1rem;
            padding: 0.45rem 0.55rem;
            font-size: 0.62rem;
            line-height: 1.1;
        }

        .technical-request-section h6 i {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            font-size: 0.68rem;
        }

        .technical-request-section .p-3,
        .technical-request-section {
            padding: 0.65rem !important;
        }

        .technical-request-card label {
            font-size: 0.66rem;
            line-height: 1.15;
            margin-bottom: 0.18rem;
        }

        .technical-request-card .form-group,
        .technical-request-card .mb-3 {
            margin-bottom: 0.48rem !important;
        }

        .technical-request-section .form-control,
        .technical-request-section .bootstrap-select > .dropdown-toggle {
            min-height: 32px;
            height: 32px;
            padding: 0.28rem 0.45rem;
            border-radius: 7px;
            font-size: 0.72rem;
            line-height: 1.15;
        }

        .technical-request-section textarea.form-control {
            height: auto;
            min-height: 68px;
            line-height: 1.3;
        }

        .technical-request-section .bootstrap-select,
        .technical-request-section .bootstrap-select > .dropdown-toggle {
            width: 100% !important;
        }

        .technical-request-section .bootstrap-select .filter-option-inner,
        .technical-request-section .bootstrap-select .filter-option-inner-inner {
            font-size: 0.72rem;
            line-height: 1.15;
        }

        .technical-request-section small.text-muted,
        .technical-request-card .text-muted,
        .technical-request-card .small {
            font-size: 0.62rem;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        #store_summary {
            padding: 0.45rem !important;
            margin-bottom: 0.48rem !important;
            border-radius: 7px !important;
        }

        .technical-request-actions {
            align-items: stretch;
            flex-direction: row;
            gap: 5px;
            margin: 0.55rem -0.55rem -0.55rem -0.55rem;
            padding: 0.5rem 0.55rem;
        }

        .technical-request-actions .btn {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
            padding: 0.36rem 0.42rem;
            border-radius: 7px;
            font-size: 0.68rem;
            line-height: 1.1;
            white-space: normal;
        }

        .technical-request-files-list {
            grid-template-columns: 1fr;
            gap: 6px;
            margin-top: 0.45rem;
        }

        .technical-request-file-item {
            padding: 0.45rem;
            gap: 6px;
        }

        .technical-request-file-thumb,
        .technical-request-file-icon {
            width: 32px;
            height: 32px;
            flex-basis: 32px;
            border-radius: 6px;
        }

        .technical-request-file-name {
            font-size: 0.68rem;
        }

        .technical-request-file-actions .btn {
            padding: 0.22rem 0.35rem;
            border-radius: 6px;
            font-size: 0.62rem;
        }
    }
</style>
