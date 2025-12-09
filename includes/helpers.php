<?php
// includes/helpers.php

/**
 * Genera un slug único para una tabla dada.
 *
 * IMPORTANTE:
 *  - $table y $idField deben ser nombres de tabla/campo válidos (sin caracteres raros).
 *  - No se usan como parámetros, por eso se validan con regex antes de interpolarlos.
 *
 * @param PDO      $pdo        Conexión PDO
 * @param string   $table      Nombre de la tabla (ej: 'nzk_videos')
 * @param string   $baseSlug   Slug base ya normalizado (ej: 'mi-programa')
 * @param string   $idField    Nombre del campo ID (ej: 'id')
 * @param int|null $excludeId  ID a excluir (cuando editas un registro)
 *
 * @return string  Slug único
 */
function generateUniqueSlug(
    PDO $pdo,
    string $table,
    string $baseSlug,
    string $idField = 'id',
    ?int $excludeId = null
): string {
    // Validar nombre de tabla y campo para evitar inyección en identificadores
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        throw new InvalidArgumentException('Nombre de tabla no válido en generateUniqueSlug().');
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $idField)) {
        throw new InvalidArgumentException('Nombre de campo ID no válido en generateUniqueSlug().');
    }

    // Asegurar que el slug base no esté vacío
    $baseSlug = trim($baseSlug);
    if ($baseSlug === '') {
        $baseSlug = 'item';
    }

    $slug = $baseSlug;
    $i    = 1;

    while (true) {
        // Construir el SQL con identificadores validados y backticks
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE slug = :slug";
        if ($excludeId !== null) {
            $sql .= " AND `{$idField}` != :id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $exists = (int)$stmt->fetchColumn() > 0;

        if (!$exists) {
            return $slug;
        }

        // Si existe, probar con sufijos: mi-programa-1, mi-programa-2, etc.
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}
