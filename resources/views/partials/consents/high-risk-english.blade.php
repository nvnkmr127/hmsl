<div style="border: 2px solid #111827; border-radius: 16px; padding: 18px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap: 16px; margin-bottom: 14px;">
        <div style="font-size: 10pt; color:#374151;">
            Doctor Name: <span style="font-weight: 900;">{{ $doctorName ?? '—' }}</span>
        </div>
        <div style="background:#111827; color:#fff; padding: 10px 14px; border-radius: 14px; font-weight: 900; letter-spacing: 0.04em; text-align:right;">
            <div style="font-size: 12pt;">HIGH RISK CONSENT</div>
        </div>
    </div>

    <div style="border: 1px solid #111827; border-radius: 14px; padding: 14px; margin-bottom: 14px;">
        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap: 10px; font-size: 11pt;">
            <div>
                Patient Name: <span style="font-weight: 900;">{{ $patient->full_name }}</span>
            </div>
            <div style="text-align:right;">
                (Age): <span style="font-weight: 900;">{{ $patient->age ?? '—' }}</span>
            </div>
            <div>
                UHID: <span style="font-weight: 900;">{{ $patient->uhid ?? '—' }}</span>
            </div>
            <div style="text-align:right;">
                Phone: <span style="font-weight: 900;">{{ $patient->phone ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div style="font-size: 12pt; line-height: 1.85;">
        <p style="margin: 0 0 12px;">
            The doctor has clearly explained to us that during the treatment/procedure for our patient
            <span style="font-weight: 900; text-decoration: underline;">{{ $patient->full_name }}</span>,
            there is a possibility of serious complications and life risk.
            We have understood this and we are giving this consent voluntarily.
        </p>

        <p style="margin: 0 0 12px;">
            If any unexpected complication, risk, or life-threatening situation occurs during the treatment,
            we agree that we will not hold the doctor, hospital staff, or hospital management responsible,
            and we will not claim any compensation.
        </p>

        <p style="margin: 0;">
            We are signing this consent after understanding the above.
        </p>
    </div>

    <div style="margin-top: 24px; display:flex; justify-content:flex-end;">
        <div style="width: 240px; text-align:center; font-size: 12pt; font-weight: 900;">
            Signature / Thumb Impression
        </div>
    </div>

    <div style="margin-top: 18px; font-size: 12pt;">
        <div style="font-weight: 900; margin-bottom: 10px;">Relationship with Patient</div>

        <div style="display:flex; gap: 12px; align-items:center; margin-bottom: 12px;">
            <div style="width: 72px; font-weight: 800;">Name:</div>
            <div style="flex:1; border-bottom: 1px solid #111827; height: 18px;"></div>
        </div>
        <div style="display:flex; gap: 12px; align-items:center;">
            <div style="width: 72px; font-weight: 800;">Mobile:</div>
            <div style="flex:1; border-bottom: 1px solid #111827; height: 18px;"></div>
        </div>
    </div>
</div>

