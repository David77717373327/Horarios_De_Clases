/* ============================================ */
/* CARDS GRID */
/* ============================================ */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

/* ============================================ */
/* ENTITY CARD */
/* ============================================ */
.entity-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}

.entity-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.1);
}

/* ============================================ */
/* ZONA SUPERIOR: ACENTO DE COLOR LATERAL */
/* ============================================ */
.entity-card-main {
    display: flex;
    align-items: stretch;
    flex: 1;
}

/* Franja lateral de color */
.entity-accent {
    width: 72px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 0;
    flex-shrink: 0;
    gap: 0.5rem;
}

.entity-card[data-tipo="profesor"] .entity-accent { background: #2563eb; }
.entity-card[data-tipo="materia"]  .entity-accent { background: #059669; }
.entity-card[data-tipo="grado"]    .entity-accent { background: #d97706; }

.entity-accent-icon {
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.1rem;
}

.entity-accent-count {
    font-size: 0.625rem;
    font-weight: 700;
    color: rgba(255,255,255,0.75);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    line-height: 1.2;
}

/* ============================================ */
/* CONTENIDO DERECHO */
/* ============================================ */
.entity-card-content {
    flex: 1;
    padding: 1.1rem 1.1rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.entity-name {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.75rem 0;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Stats en línea horizontal */
.entity-stats {
    display: flex;
    gap: 0.5rem;
}

.entity-stat {
    flex: 1;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 0.6rem 0.5rem;
    text-align: center;
}

.entity-stat-value {
    font-size: 1.375rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 0.2rem;
    letter-spacing: -0.3px;
}

.entity-card[data-tipo="profesor"] .entity-stat-value { color: #2563eb; }
.entity-card[data-tipo="materia"]  .entity-stat-value { color: #059669; }
.entity-card[data-tipo="grado"]    .entity-stat-value { color: #d97706; }

.entity-stat-label {
    font-size: 0.6rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

/* ============================================ */
/* FOOTER */
/* ============================================ */
.entity-footer {
    padding: 0.625rem 1.1rem 0.625rem 1.1rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.entity-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    font-size: 0.72rem;
    font-weight: 600;
    border-radius: 999px;
}

.entity-badge i { font-size: 0.4rem; }

.entity-badge.badge-complete { background: #dcfce7; color: #15803d; }
.entity-badge.badge-partial  { background: #fef9c3; color: #a16207; }
.entity-badge.badge-pending  { background: #f1f5f9; color: #64748b; }

.entity-action {
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: gap 0.15s ease;
}

.entity-card[data-tipo="profesor"] .entity-action { color: #2563eb; }
.entity-card[data-tipo="materia"]  .entity-action { color: #059669; }
.entity-card[data-tipo="grado"]    .entity-action { color: #d97706; }

.entity-card:hover .entity-action { gap: 0.5rem; }