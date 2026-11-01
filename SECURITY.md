# Security Policy

We take the security of LimeSurvey seriously and welcome reports from security researchers, users, and the community.

## Reporting a Vulnerability

If your report concerns an issue with any of the LimeSurvey websites (https://*.limesurvey.org) or LimeSurvey Cloud, please contact support@limesurvey.org.

For LimeSurvey Community Edition (CE), please report (suspected) security vulnerabilities by creating a bug report on https://bugs.limesurvey.org (you might need to register an account there first) and marking it as severity **security** and/or category **Security**. 
This is necessary to ensure proper reaction time.

Please do not disclose the issue publicly until we have had the opportunity to investigate and, where applicable, release a fix.

To help us triage your report as quickly as possible, please include where you can:

- A description of the vulnerability and its potential impact
- If the vulnerability is exploited, already.
- Steps to reproduce, or a proof-of-concept
- The affected LimeSurvey version(s) and edition (Community/Cloud)

## What to Expect From Us

- **Acknowledgement:** You will receive a response from us within 48 hours.
- **Triage and severity assessment:** Once we confirm the issue, we assess and assign it a severity rating (based on CVSS).
- **Remediation:** We aim to release a fix as soon as possible depending on complexity — historically within a few days for confirmed, high-severity issues.
- **Coordinated disclosure:** We ask that you do not publicly disclose the vulnerability until a fix is available, or for 90 days after your report, whichever comes first — unless we agree on a different timeline together.
- **Public disclosure of fixes:** Once a fix is released, we publish a security advisory describing the vulnerability, its severity, and the affected and fixed versions at [link to changelog / security advisories page].

## CVE Identifiers

We do not currently cooperate with a CVE Numbering Authority (CNA). Once we have confirmed a vulnerability, you are welcome to request a CVE for it yourself (for example via MITRE or a partner CNA), and we will provide the technical details needed to support that request.

## Supported Versions and Security Updates

Security updates are provided **free of charge** for all currently supported LimeSurvey release lines. See https://www.limesurvey.org/manual/LimeSurvey_roadmap for the current support end date for each version.

## Software Bill of Materials (SBOM)

A Software Bill of Materials for each release is available as part of the ComfortUpdate Extension package (see https://community.limesurvey.org/comfort-update-extension/ ), to help you assess exposure to vulnerabilities in third-party components we depend on.

## Known Findings in Bundled Third-Party Components

### CKEditor 4 (bundled version 4.22.1)

LimeSurvey bundles CKEditor 4.22.1, the last version released under an open-source license (GPL/LGPL/MPL). All later 4.x releases are published only under a commercial "CKEditor 4 LTS" license, so automated dependency and vulnerability scanners will flag our bundled version against CVEs that were fixed in 4.23.0-lts and later. We have reviewed each publicly disclosed CVE against our actual plugin set and configuration:

| CVE | Summary | Fixed in | Applicable to LimeSurvey? |
|---|---|---|---|
| CVE-2024-43407 | XSS via the bundled GeSHi library in the Code Snippet plugin | 4.25.0 | No — the Code Snippet / GeSHi plugin is not bundled or enabled in our CKEditor build. |
| CVE-2024-37888 | XSS in the Open Link plugin via unsanitized `javascript:` URIs | Open Link 1.0.5 | No — the `openlink` plugin is not bundled or enabled; we use only the standard `link` plugin. |
| CVE-2024-43411 | XSS via a compromised `cke4.ckeditor.com` domain, reachable only when the editor's version-check/notification feature is enabled | 4.25.0-lts | No — `versionCheck` is explicitly disabled in our editor configuration. |
| CVE-2024-24815 | Malformed HTML using CDATA sections can bypass CKEditor's client-side Advanced Content Filtering (ACF) in full-page editing mode | 4.24.0-lts | No additional risk — see below. |

**Why the CDATA/ACF bypass (CVE-2024-24815) does not create risk in LimeSurvey:** CKEditor's client-side content filtering is not our security boundary for HTML input. All admin-authored HTML — question text, survey welcome/end/policy text, and email templates, including the fields that use CKEditor's full-page editing mode — is passed through a server-side sanitizer (HTMLPurifier, invoked via a Yii model validator) as part of saving the record, independently of and in addition to whatever the client-side editor did or did not allow through. A bypass of the editor's own filter therefore does not translate into stored or reflected XSS for standard user roles. Content authored by a Superadmin is intentionally exempt from this filter, consistent with a Superadmin already having unrestricted HTML/script authoring capability elsewhere in the application by design — this is a deliberate trust boundary, not a defect.

We track replacement of CKEditor 4 on our roadmap. In the meantime, we do not consider the CVEs above to represent an exploitable risk in supported LimeSurvey deployments. If you have a proof-of-concept showing otherwise — in particular, any way to turn the CDATA/ACF bypass into script execution that survives our server-side sanitizer, or that affects a role other than Superadmin — please report it through the process described above; we will treat it as a priority.

## Recognition

Thank you for practicing coordinated disclosure — we appreciate the work the security research community does to keep LimeSurvey and its users safe.
