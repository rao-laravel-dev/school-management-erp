{{-- Shared CSS for the new Marksheet / Report Card designs (Modern, Formal). Include ONCE per print page, before the sheets.
     Colours come from CSS variables set on every .ep-page: --p (main), --on (text on main), --a (accent) --}}
<style>
    .ep-page { position: relative; overflow: hidden; background: #fff center / cover no-repeat; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1a1a1a; display: flex; flex-direction: column; box-sizing: border-box; }
    .ep-page * { box-sizing: border-box; }
    /* A4 minus the 10mm print margin; 1mm less than the full height so a blank 2nd page never appears */
    .ep-page.portrait { width: 190mm; height: 276mm; }
    .ep-page.landscape { width: 277mm; height: 189mm; }
    .ep-page + .ep-page { page-break-before: always; break-before: page; }

    @media screen {
        body.ep-preview { background: #eee; padding: 10px; }
        .ep-page { margin: 0 auto 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, .25); }
    }
    @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .ep-page { margin: 0; box-shadow: none; }
    }

    /* header: logo | school + lines | logo */
    /* old: .ep-head { display: flex; align-items: center; gap: 5mm; }. Now a 3 part grid: equal left/right slots, so the name block is centred on the page */
    .ep-head { display: grid; grid-template-columns: 45mm minmax(0, 1fr) 45mm; align-items: center; gap: 5mm; }
    /* old: .ep-head-side width 24mm, .ep-logo max 24 x 20mm (school logo looked tiny). Both sides share one width so the school name stays centred */
    /* old (before logo upsize): .ep-head-side width 40mm, .ep-logo max 40 x 24mm. Now ~30mm tall logo, 45mm wide max (very wide logos are limited by width) */
    .ep-head-side { width: 45mm; flex: none; display: flex; align-items: center; justify-content: center; }
    .ep-head-main { flex: 1; text-align: center; min-width: 0; }
    .ep-logo { max-width: 45mm; max-height: 30mm; object-fit: contain; display: block; }
    .ep-header-image { display: block; width: 100%; max-height: 26mm; object-fit: contain; }
    /* old: no nowrap (name wrapped to 2-3 lines). Now one line; the print script shrinks the font to fit (min 14pt), ellipsis only if still too long */
    .ep-school { margin: 0; font-size: 22pt; font-weight: bold; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ep-line { margin: 1mm 0 0; font-size: 11pt; }
    .ep-contact { margin: 1mm 0 0; font-size: 8.5pt; opacity: .85; }
    .ep-doc-title { text-align: center; font-weight: bold; letter-spacing: 2pt; text-transform: uppercase; font-size: 11pt; }

    /* student block */
    .ep-info { display: flex; justify-content: space-between; gap: 6mm; align-items: flex-start; }
    .ep-info-grid { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 1mm 6mm; }
    .ep-field { display: flex; gap: 2mm; font-size: 10.5pt; }
    .ep-field .lbl { font-weight: bold; white-space: nowrap; }
    .ep-field .val { min-width: 0; overflow-wrap: anywhere; }
    .ep-photo { width: 24mm; height: 28mm; object-fit: cover; border: .3mm solid #999; flex: none; }
    .ep-body-text { line-height: 1.5; font-size: 10.5pt; }

    /* marks table + summary */
    table.ep-table { width: 100%; border-collapse: collapse; }
    table.ep-table th, table.ep-table td { border: .25mm solid #444; padding: 1.4mm 2mm; text-align: center; font-size: 10.5pt; }
    table.ep-table th { background: var(--p); color: var(--on); }
    table.ep-table td.l, table.ep-table th.l { text-align: left; }
    table.ep-table tfoot td { font-weight: bold; background: rgba(0, 0, 0, .06); }
    .ep-summary { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; border: .25mm solid #444; }
    .ep-summary > div { padding: 1.4mm 2mm; text-align: center; font-size: 10.5pt; border-left: .25mm solid #444; }
    .ep-summary > div:first-child { border-left: 0; }
    .ep-summary .k { display: block; font-size: 8pt; text-transform: uppercase; letter-spacing: .5pt; color: #555; }
    .ep-summary .v { font-weight: bold; }

    /* signatures */
    .ep-sign-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 6mm; }
    .ep-sign-block { width: 31%; text-align: center; font-size: 10pt; }
    .ep-sign-img { display: block; margin: 0 auto 1mm; max-height: 15mm; max-width: 45mm; object-fit: contain; }
    .ep-stamp { display: block; margin: 0 auto 1mm; max-height: 22mm; max-width: 22mm; object-fit: contain; }
    .ep-sign-line { border-top: .3mm solid #333; padding-top: 1mm; margin-top: 1mm; }
    .ep-footer-text { text-align: center; font-size: 8.5pt; color: #555; }

    /* landscape: less height, so smaller header / photo and a 3 column student block */
    .ep-page.landscape .ep-school { font-size: 16pt; }
    /* old (before logo upsize): landscape side 30mm, logo max 30 x 18mm */
    .ep-page.landscape .ep-head-side { width: 60mm; }
    .ep-page.landscape .ep-head { grid-template-columns: 60mm minmax(0, 1fr) 60mm; }
    .ep-page.landscape .ep-logo { max-width: 60mm; max-height: 22mm; }
    .ep-page.landscape .ep-header-image { max-height: 16mm; }
    .ep-page.landscape .ep-photo { width: 18mm; height: 22mm; }
    .ep-page.landscape .ep-info-grid { grid-template-columns: 1fr 1fr 1fr; }
    .ep-page.landscape .ep-sign-img { max-height: 10mm; }
    .ep-page.landscape .ep-stamp { max-height: 14mm; }
    .ep-page.landscape table.ep-table th, .ep-page.landscape table.ep-table td { padding: .9mm 2mm; font-size: 9.5pt; }

    /* Modern/Formal extras: failed subject in red, words line + 4 summary boxes, grade scale strip, 4 signature slots */
    .ep-fail { color: #c0392b; font-weight: bold; }
    .ep-lower { display: flex; flex-direction: column; gap: 3.5mm; }
    /* landscape has little height: summary boxes (left) and grade scale (right) share one row, the words line spans both */
    .ep-page.landscape .ep-lower.two { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5mm 4mm; align-items: center; }
    .ep-page.landscape .ep-lower.two .ep-words { grid-column: 1 / -1; margin-bottom: 0; }
    .ep-page.landscape .ep-lower.two table.ep-grades th { width: 14mm; }
    .ep-page.landscape table.ep-grades th, .ep-page.landscape table.ep-grades td { font-size: 7.5pt; }
    .ep-page.landscape.ex .ep-summary > div { padding: .8mm 2mm; }
    /* landscape + extra sections: tighter spacing everywhere so the sheet fits one page */
    .ep-page.landscape.ex .mo-band { padding: 3mm 10mm 2.5mm; }
    .ep-page.landscape.ex .mo-main { gap: 2mm; padding-top: 2mm; }
    .ep-page.landscape.ex .mo-foot { padding: 1mm 10mm 2mm; gap: 1mm; }
    .ep-page.landscape.ex .fo-frame { gap: 2mm; padding: 3.5mm 9mm 3mm; }
    .ep-page.landscape.ex .fo-head { padding-bottom: 1.5mm; }
    .ep-page.landscape.ex .fo-main { gap: 2mm; }
    .ep-page.landscape.ex .ep-doc-title { font-size: 10pt; letter-spacing: 1.5pt; }
    .ep-page.landscape.ex .ep-info { gap: 4mm; }
    .ep-page.landscape.ex .ep-info-grid { gap: .3mm 5mm; }
    .ep-page.landscape.ex .ep-field { font-size: 9pt; }
    .ep-page.landscape.ex .ep-photo { width: 14mm; height: 17mm; }
    .ep-page.landscape.ex .ep-body-text { font-size: 9pt; line-height: 1.25; }
    .ep-page.landscape.ex .ep-contact { margin-top: .5mm; font-size: 8pt; }
    .ep-page.landscape.ex .ep-sign-img { max-height: 8mm; }
    .ep-page.landscape.ex .ep-stamp { max-height: 10mm; }
    .ep-page.landscape.ex .ep-sign-line { margin-top: .5mm; padding-top: .5mm; font-size: 9pt; }
    .ep-page.landscape.ex .ep-footer-text { font-size: 7.5pt; }
    .ep-page.landscape.lv3 table.ep-table th, .ep-page.landscape.lv3 table.ep-table td { font-size: 7.5pt; padding: .1mm 1mm; }
    .ep-page.landscape.lv3 .ep-photo { display: none; }
    .ep-page.landscape.lv3 .ep-field, .ep-page.landscape.lv3 .ep-body-text { font-size: 8pt; }
    .ep-words { font-size: 8.5pt; color: #444; margin-bottom: -2mm; }
    .ep-summary.boxes { grid-template-columns: repeat(4, 1fr); }
    table.ep-grades { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.ep-grades th, table.ep-grades td { border: .25mm solid #888; padding: .6mm 1mm; text-align: center; font-size: 8.5pt; }
    table.ep-grades th { background: rgba(0, 0, 0, .06); width: 20mm; }
    .ep-sign-row.four .ep-sign-block { width: 23%; }
    .ep-page.lv2 table.ep-grades th, .ep-page.lv2 table.ep-grades td { font-size: 7.5pt; padding: .3mm .8mm; }

    /* many subjects: tighter rows so the sheet still fits ONE page */
    .ep-page.lv1 table.ep-table th, .ep-page.lv1 table.ep-table td { padding: .8mm 1.5mm; font-size: 9.5pt; }
    .ep-page.lv2 table.ep-table th, .ep-page.lv2 table.ep-table td { padding: .3mm 1.2mm; font-size: 8.5pt; }
    .ep-page.lv1 .ep-field, .ep-page.lv1 .ep-body-text { font-size: 9.5pt; }
    .ep-page.lv2 .ep-field, .ep-page.lv2 .ep-body-text { font-size: 8.5pt; line-height: 1.3; }
</style>
