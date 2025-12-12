-- Migration: Create prospect notes table
-- Date: 2025-12-11
-- Description: Creates table for collaborative notes on prospects

CREATE TABLE IF NOT EXISTS tija_prospect_notes (
    prospectNoteID INT AUTO_INCREMENT PRIMARY KEY,
    salesProspectID INT NOT NULL,
    noteContent TEXT NOT NULL,
    noteType ENUM('general', 'guidance', 'warning', 'success') DEFAULT 'general',
    isPrivate ENUM('Y', 'N') DEFAULT 'N',
    createdByID INT NOT NULL,
    DateAdded DATETIME DEFAULT CURRENT_TIMESTAMP,
    LastUpdatedByID INT NULL,
    LastUpdate DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    Lapsed ENUM('Y', 'N') DEFAULT 'N',
    Suspended ENUM('Y', 'N') DEFAULT 'N',

    INDEX idx_prospect (salesProspectID),
    INDEX idx_created_by (createdByID),
    INDEX idx_date (DateAdded),

    FOREIGN KEY (salesProspectID) REFERENCES tija_sales_prospects(salesProspectID) ON DELETE CASCADE,
    FOREIGN KEY (createdByID) REFERENCES people(ID),
    FOREIGN KEY (LastUpdatedByID) REFERENCES people(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
