<style>
.mkt-wrap{
    display:flex;
    flex-direction:column;
    gap:14px;
}
.mkt-card{
    background:#fff;
    border:1px solid #e5eaf3;
    border-radius:24px;
    box-shadow:0 12px 28px rgba(16,24,40,.055);
    overflow:hidden;
}
.mkt-head{
    padding:16px;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    background:linear-gradient(180deg,#ffffff,#f8fbff);
    border-bottom:1px solid #eef2f7;
}
.mkt-title b{
    display:block;
    font-size:18px;
    font-weight:950;
    color:#101828;
}
.mkt-title span{
    display:block;
    margin-top:4px;
    font-size:12px;
    font-weight:750;
    color:#667085;
}
.mkt-body{
    padding:16px;
}
.mkt-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
}
.mkt-field label{
    display:block;
    margin-bottom:7px;
    font-size:12px;
    font-weight:900;
    color:#344054;
}
.mkt-field input,
.mkt-field select{
    width:100%;
    height:44px;
    border:1px solid #e5e7eb;
    border-radius:15px;
    padding:0 12px;
    font-weight:700;
    color:#111827;
    background:#fff;
}
.mkt-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:12px;
}
.mkt-btn{
    min-height:40px;
    border:0;
    border-radius:14px;
    padding:0 14px;
    font-size:12px;
    font-weight:950;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    cursor:pointer;
}
.mkt-btn.primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    box-shadow:0 10px 22px rgba(37,99,235,.18);
}
.mkt-btn.light{
    background:#eff6ff;
    color:#1d4ed8;
    border:1px solid #dbeafe;
}
.mkt-table-wrap{
    overflow:auto;
}
.mkt-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    min-width:760px;
}
.mkt-table th{
    text-align:left;
    padding:12px;
    background:#f8fafc;
    border-bottom:1px solid #e5e7eb;
    color:#64748b;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.mkt-table td{
    padding:12px;
    border-bottom:1px solid #eef2f7;
    color:#111827;
    font-size:13px;
    font-weight:650;
}
.mkt-pill{
    display:inline-flex;
    align-items:center;
    min-height:24px;
    border-radius:999px;
    padding:4px 9px;
    background:#f1f5f9;
    color:#475569;
    font-size:11px;
    font-weight:900;
}
.mkt-pill.green{
    background:#ecfdf3;
    color:#027a48;
}
.mkt-pill.red{
    background:#fef3f2;
    color:#b42318;
}
.mkt-filter{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}
.mkt-filter select{
    height:42px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:0 12px;
    min-width:220px;
    font-weight:800;
    background:#fff;
}
@media(max-width:760px){
    .mkt-grid{
        grid-template-columns:1fr;
    }
    .mkt-head{
        flex-direction:column;
    }
    .mkt-filter{
        width:100%;
    }
    .mkt-filter select,
    .mkt-filter .mkt-btn{
        width:100%;
    }
}
</style>
