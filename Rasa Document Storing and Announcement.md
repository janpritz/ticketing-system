# Rasa Document Storing and Announcement

This document provides an overview of the document storage system for Rasa integration, including the directory structure and available files in the `docs/` directory.

## Directory Structure

```
docs/
├── ACC Administration and Staff.txt
├── ACC Links.txt
├── Announcements.txt
├── Extension Services.txt
├── General Policy.txt
├── OSA.txt
├── Research and Development.txt
├── Research and Extension Services.txt
├── Student Manual pdf.txt
├── Student Publication.txt
├── Student Services.txt
└── Undergraduate Programs.txt
```

## File Descriptions

### ACC Administration and Staff.txt
Contains information about the administration and staff of Abuyog Community College (ACC), including positions, names, and contact details.

### ACC Links.txt
Provides important links related to ACC, such as student portal, e-library, and official website.

### Announcements.txt
Stores announcements from the college, formatted with IDs, titles, and content. Used for parsing and displaying current announcements.

**Format:**
```
id: [number]
title: [title text]
[content text]
---------
```

**Example:**
```
id: 1
title: ENROLLMENT SCHEDULE FOR SECOND SEMESTER
ENROLLMENT SCHEDULE FOR SECOND SEMESTER, AY 2025–2026 is officially announced!
To ALL ACC Students:
     To comply with the CHED-mandated June 2026 opening of classes for AY 2026–2027, the following enrollment schedule has been set.
     Thank you for understanding.
     -College President
     🗓 Enrollment Period:
     👉 December 9–23, 2025
     📌 IMPORTANT DATES TO REMEMBER
     🔹 College Admission Test: December 1–5, 2025
     🔹 Enrollment per Program: See individual program schedule
     🔹 Last Day of Enrollment: December 29, 2025
     🔹 Student Orientation (for New Students, Transferees & Returnees): January 5, 2026
     🔹 Start of Formal Classes: January 5, 2026
     🔹 Adding/Dropping of Subjects: January 15–16, 2026
     Make sure to complete your enrollment within the given period. We look forward to seeing you this coming semester! 💚💛
---------
```

### Extension Services.txt
Details the extension services offered by ACC, including objectives for community organization, educational programs, socio-economic programs, health and nutrition, and environmental programs.

### General Policy.txt
Outlines the general admission policies, entrance credentials, enrollment procedures, and rules for various student categories.

### OSA.txt
Information about the Office of Student Affairs (OSA), including goals, services offered, and details about the Supreme Student Council.

### Research and Development.txt
Comprehensive documentation of ACC's research agenda, operational procedures, criteria for research prioritization, and monitoring/evaluation processes.

### Research and Extension Services.txt
Details the vision, mission, goals, and objectives of the Office of Research Coordinator for Research Development and Extension Services.

### Student Manual pdf.txt
The student manual in text format, covering academic policies, admission requirements, enrollment procedures, student conduct, and various college services.

### Student Publication.txt
Information about student publications, including the voice of students, specific objectives, editorial staff, and transparency seal.

### Student Services.txt
Overview of student services including guidance and counseling, health services, library policies, alumni services, and support for special groups.

### Undergraduate Programs.txt
Details the undergraduate programs offered by ACC's colleges: Arts, Science, and Education; Management and Entrepreneurship; Criminal Justice Education; and Information Technology Education.

## Usage in Rasa

These documents are used by the FAQ updater service (`programs/faq_updater.py`) to:

- Store and retrieve FAQ data
- Generate dynamic actions for Rasa
- Handle document uploads and updates
- Parse announcements for chatbot responses
- Provide information retrieval for various college-related queries

## API Endpoints

The FAQ updater service provides several endpoints for interacting with these documents:

- `/list-docs`: List all text files in the docs directory
- `/download/<filename>`: Download specific file content
- `/download-announcements`: Parse and return structured announcements
- `/update-document`: Update document content
- `/upload-file`: Upload new files to docs directory

All endpoints require authentication via `X-FAQ-UPDATER-TOKEN` header or `token` query parameter.