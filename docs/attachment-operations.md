# Attachment operations

This is a factual description of current attachment behavior. It is not a
malware-scanning or retention policy; those decisions require an owner.

## Implemented controls

- Uploads go through `/api/attachments.php` and shared validation in
  `app/attachments.php`. Supported files are PDF, PNG, JPEG, macro-free OOXML
  Word/Excel/PowerPoint, and CSV. Extension and detected content are checked;
  Office packages are inspected for expected entries and rejected if macro,
  ActiveX, or traversal-like entries are found.
- The maximum upload size is the lower of 5 MB and the PHP upload limits.
  Workspaces are limited to 20 files per record and 100 MB total.
- Storage uses a random 64-character hexadecimal name under the private
  `storage/attachments/` directory. The original filename and file metadata are
  stored in `record_attachments`; SHA-256 is recorded for integrity checks.
- Downloads require the relevant authenticated account and record access.
  Business workspaces are tenant-scoped. Downloads are attachment-only,
  `no-store`, and sandboxed; the private storage directory must not be served
  directly by the web server.
- Removing a regular record attachment removes its stored file and metadata.
  Candidate-submitted resumes are preserved with their application; withdrawal
  ends the employer's in-app download access but does not erase the submitted
  record or copies already downloaded.
- The local-only backup drill copies private attachment files and verifies
  their hashes alongside database table contents. This does not constitute a
  production backup configuration or retention schedule.

## Known gaps and owner decisions

- Uploads are **not antivirus-scanned** and there is no quarantine workflow.
  Do not describe the upload path as malware-safe. The owner must approve a
  scanning/quarantine service or explicitly accept the risk before launch.
- Retention and deletion periods for ordinary attachments, application
  snapshots and withdrawn resumes have not been approved. Do not promise
  deletion of an application or previously downloaded copy.
- The business/hosting owner must assign operational responsibility for storage
  capacity, filesystem permissions, backup monitoring, restore testing, and
  privacy requests involving files.
- The application has a per-company quota, but no configured infrastructure
  capacity or load budget has been established for the deployment host.

## Safe operations

- Keep `storage/attachments/` private and persistent. Never expose it as a
  static web directory or include file contents in logs, tickets or diagnostics.
- Back up the database metadata and file directory together. Verify file hashes
  after restore before considering that restore successful.
- Use only synthetic files for local tests. Do not send customer uploads to an
  external scanning service until the owner approves the service and its data
  handling.
- If an upload or download fails, record the request time, non-secret error
  reference, record type and attachment ID if available. Do not ask customers
  to email resumes, bank records or other sensitive files as a workaround.
