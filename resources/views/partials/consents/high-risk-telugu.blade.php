<div style="border: 2px solid #111827; border-radius: 16px; padding: 18px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap: 16px; margin-bottom: 14px;">
        <div style="font-size: 10pt; color:#374151;">
            డాక్టరు గారు పేరు: <span style="font-weight: 900;">{{ $doctorName ?? '—' }}</span>
        </div>
        <div style="background:#111827; color:#fff; padding: 10px 14px; border-radius: 14px; font-weight: 900; letter-spacing: 0.04em; text-align:right;">
            <div style="font-size: 12pt;">హై రిస్క్ కన్సెంట్</div>
            <div style="font-size: 9pt; font-weight: 800; opacity: 0.9;">HIGH RISK CONSENT</div>
        </div>
    </div>

    <div style="border: 1px solid #111827; border-radius: 14px; padding: 14px; margin-bottom: 14px;">
        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap: 10px; font-size: 11pt;">
            <div>
                పేషెంట్ పేరు: <span style="font-weight: 900;">{{ $patient->full_name }}</span>
            </div>
            <div style="text-align:right;">
                (పేరు / వయసు): <span style="font-weight: 900;">{{ $patient->age ?? '—' }}</span>
            </div>
            <div>
                UHID: <span style="font-weight: 900;">{{ $patient->uhid ?? '—' }}</span>
            </div>
            <div style="text-align:right;">
                ఫోన్: <span style="font-weight: 900;">{{ $patient->phone ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div style="font-size: 12pt; line-height: 1.85;">
        <p style="margin: 0 0 12px;">
            డాక్టరు గారు మా పేషెంట్ <span style="font-weight: 900; text-decoration: underline;">{{ $patient->full_name }}</span>
            కు చికిత్స సమయంలో కూడా ప్రాణా ప్రమాదం తలెత్తి జరిగే అవకాశం ఉందని మా కు అర్థం అయ్యే విధంగా డాక్టరు గారు చెప్పారు.
            అయినా సరే ఆ విషయము స్వచ్ఛందంగా విని అర్థం చేసుకొని, అంగీకరించి, అంగీకరిస్తున్నాము.
        </p>

        <p style="margin: 0 0 12px;">
            ఈ చికిత్స/ప్రక్రియలో ఏ విధమైన ప్రమాదము గాని, ప్రాణాపాయము గాని, హాస్పిటల్ సిబ్బంది గాని అనుకోని విధంగా జరిగి,
            దానికి గాను డాక్టరు గారిని గాని, హాస్పిటల్ సిబ్బంది గాని, హాస్పిటల్ నిర్వాహకులను గాని బాధ్యులుగా చేయబోమని
            ఎటువంటి నష్టపరిహారం కోరబోమని మేము అంగీకరిస్తున్నాము.
        </p>

        <p style="margin: 0;">
            మేము అర్థం అయ్యే విధంగా ఈ హామీ ఇస్తూ సంతకం చేయుచున్నాము.
        </p>
    </div>

    <div style="margin-top: 24px; display:flex; justify-content:flex-end;">
        <div style="width: 240px; text-align:center; font-size: 12pt; font-weight: 900;">
            సంతకం / వేలిముద్ర
        </div>
    </div>

    <div style="margin-top: 18px; font-size: 12pt;">
        <div style="font-weight: 900; margin-bottom: 10px;">పేషెంట్‌తో సంబంధం</div>

        <div style="display:flex; gap: 12px; align-items:center; margin-bottom: 12px;">
            <div style="width: 64px; font-weight: 800;">పేరు :</div>
            <div style="flex:1; border-bottom: 1px solid #111827; height: 18px;"></div>
        </div>
        <div style="display:flex; gap: 12px; align-items:center;">
            <div style="width: 64px; font-weight: 800;">సెల్ నం. :</div>
            <div style="flex:1; border-bottom: 1px solid #111827; height: 18px;"></div>
        </div>
    </div>
</div>
