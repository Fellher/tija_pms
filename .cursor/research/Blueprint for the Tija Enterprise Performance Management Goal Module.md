# Blueprint for the Tija Enterprise Performance Management Goal Module

## 1\. Executive Overview and Strategic Imperative

The Tija Practice Management System aims to redefine the architecture of human capital management within the context of a hyper-complex, globalized enterprise. The directive is clear: create a performance management ecosystem that seamlessly integrates corporate-level Human Resource Management Systems (HRMS) with the agility required to manage global group-level organizations spanning continents, currencies, and cultures. This blueprint serves as the definitive technical and functional specification for the **Tija Goals Module**, the engine room of organizational alignment and performance execution.

In modern enterprise architectures, the disconnect between high-level strategic planning and ground-level execution remains a persistent point of failure. Traditional systems often treat performance management as a static, annual compliance exercise. Tija must reject this paradigm. Instead, it must function as a dynamic, living nervous system for the enterprise-one that translates abstract 5-year strategic visions into concrete, actionable, and measurable daily objectives for individual contributors, whether they sit in a headquarters in London, a sales office in Tokyo, or a remote development hub in Nairobi.

This report outlines a rigorous architectural approach to "Glocalization"-the capability to standardize performance metrics globally while adhering to local jurisdictional nuances and cultural performance frameworks. We will explore the implementation of a **Dual-Matrix Hierarchy** to resolve the conflict between administrative reporting (legal entities) and functional execution (project teams). We will detail the database schemas required to support high-performance cascading of goals without recursive query penalties, utilizing **Closure Table** patterns. Furthermore, we will define the **Weighted Evaluation Engine** that normalizes performance scores across diverse currencies and role expectations, ensuring that the "entire organization group's performance" can be reported on with forensic accuracy.

The resulting system will not merely track performance; it will engineer alignment. It addresses the user's specific requirements for granular cascading, multi-type goal distinction (OKR, KPI, Strategic Goal), and sophisticated evaluator assignment, all underpinned by a robust Goal Library that serves as the repository of organizational intent.

## 2\. Architectural Foundations: The Global Matrix Ecosystem

To build a performance management system for a global group-level organization, one must first architect the container in which performance occurs: the organizational structure. The user's requirement to "granulate to people performance based on the organisation strategy plan at all levels" implies a system that can traverse the graph of the organization with near-zero latency.

### 2.1 The Dual-Hierarchy Challenge

Most legacy HRMS platforms utilize a strict functional hierarchy where an employee reports to a single manager. This tree structure is insufficient for Tija's target demographic: global enterprises operating on a matrix. The research highlights that modern organizations are characterized by "Dual Reporting Lines," where employees are accountable to both functional managers for departmental goals and project managers for specific initiatives.<sup>1</sup>

Tija must essentially maintain two concurrent graphs:

- **The Administrative/Legal Hierarchy:** This defines the "Entity" relationship. It follows the flow of capital, employment contracts, and statutory compliance.
  - _Structure:_ Global Group \$\\rightarrow\$ Continental HQ \$\\rightarrow\$ Regional Office \$\\rightarrow\$ Country Legal Entity \$\\rightarrow\$ Local Branch.
  - _Purpose:_ Payroll, Compliance, Jurisdiction-specific labor laws, Statutory Reporting.
- **The Functional/Operational Hierarchy:** This defines the "Work" relationship. It follows the flow of strategy, product lines, and agile teams.
  - _Structure:_ Global Strategy Committee \$\\rightarrow\$ Global Function Head (e.g., CTO) \$\\rightarrow\$ Regional Lead \$\\rightarrow\$ Team Lead \$\\rightarrow\$ Individual.
  - _Purpose:_ Goal setting, Performance review, Mentorship, Strategy Execution.

The friction between these two hierarchies is where performance management often fails. For instance, a software engineer employed by _Tija Germany GmbH_ (Legal Entity) may work 100% of their time on a project led by a Product Manager in _Tija USA Inc_. A system that only recognizes the German legal hierarchy will force the German administrator to set goals they do not understand. Tija's architecture solves this by treating the "Reporting Line" as a dynamic attribute of the _Goal_, not just the _User_. This allows the US Manager to assign and evaluate goals for the German employee, creating a "Cross-Border Performance Instance".<sup>2</sup>

### 2.2 The Tija Organization Object Model (TOOM)

To satisfy the requirement for cascading "from the smallest entity... all the way to the global," we must formalize the granularity of the organization. The Tija Organization Object Model (TOOM) defines the distinct layers of the enterprise, each serving a unique strategic function in the goal-setting process.

| **Level ID** | **Object Type** | **Strategic Function** | **Goal Setting Responsibility** |
| --- | --- | --- | --- |
| **L0** | **Global Group** | The apex holding company. | **Visionary:** Sets 5-10 year "North Star" objectives (e.g., "Carbon Neutral by 2030"). |
| --- | --- | --- | --- |
| **L1** | **Continent/Geo** | Major geographical divisions (e.g., EMEA, APAC). | **Translation:** Adapts global strategy to broad market conditions and cultural blocks. |
| --- | --- | --- | --- |
| **L2** | **Region/Cluster** | Economic clusters (e.g., DACH, MENA). | **Allocation:** Resource distribution and regional P&L responsibility. |
| --- | --- | --- | --- |
| **L3** | **Jurisdiction** | Legal/Country boundary (e.g., France, Japan). | **Compliance:** Enforces local labor compliance (e.g., prohibiting certain KPIs in France). |
| --- | --- | --- | --- |
| **L4** | **Legal Entity** | The specific corporate body (e.g., Tija France SAS). | **Administration:** Payroll and administrative reporting center. |
| --- | --- | --- | --- |
| **L5** | **Department** | Functional grouping (e.g., Marketing). | **Tactical:** Execution of strategy via functional excellence. |
| --- | --- | --- | --- |
| **L6** | **Section/Team** | Operational unit. | **Operational:** Sprint planning and quarterly delivery. |
| --- | --- | --- | --- |
| **L7** | **Individual** | The employee. | **Execution:** Personal performance, development, and behavioral goals. |
| --- | --- | --- | --- |

Nuance in Jurisdiction (L3):

The L3 object is critical for the "multiculturally nuanced" requirement. In jurisdictions like Germany, Works Councils have legal authority to co-determine performance criteria. The L3 object in the database will contain configuration flags (e.g., requires_works_council_approval, anonymize_performance_data) that dynamically alter the UI/UX for users in those regions. For example, the system might disable "Stack Ranking" features for German entities while enabling them for US entities, ensuring global compliance within a single instance.3

### 2.3 Multi-Tenancy and Data Sovereignty

For a global system, we must address how data is stored. While the organization acts as a single group, data sovereignty laws (e.g., GDPR in Europe, CSL in China) often prevent a single physical database.

Architecture Pattern: Sharded Multi-Tenancy

We recommend a "Sharded Multi-Tenant" architecture.4

- **Logical Unity:** The application behaves as one system. A Global Admin sees a unified dashboard.
- **Physical Separation:** Data is sharded by Region or Jurisdiction. European employee goals reside in a Frankfurt data center; US goals in Virginia.
- **Federated Aggregation:** When a report is requested for "Global Performance," the system utilizes a Federated GraphQL API to query the shards in parallel. It aggregates the results in memory to present the report, ensuring that raw personal data never physically leaves its jurisdiction of origin, satisfying the requirement for "reporting on the entire organisation groups performance" without violating local laws.

## 3\. The Goals Module Blueprint: Functional Logic

The core of the request is the **Goals Module**. This is not merely a data entry form but a Strategic Alignment Engine. It must support the complex logic of cascading objectives, distinguishing between rigid goals and flexible OKRs, and managing the lifecycle of performance.

### 3.1 Distinction of Objectives: Goals vs. OKRs vs. KPIs

The user explicitly requires a way to "distinguish Global goals/objectives/KPIs for each level." Tija will implement a **Polymorphic Goal Object**. In software design patterns, polymorphism allows objects to be treated as instances of their parent class while maintaining their unique behaviors. Here, StrategicGoal, OKR, and KPI all inherit from a base PerformanceItem class but behave differently.<sup>5</sup>

#### 3.1.1 Strategic Goals (The "What")

These are qualitative, long-term visions derived from the L0/L1 levels. They are the "North Star."

- **Behavior:** Cascades via "inheritance." Lower levels must adopt the spirit of the goal but define their own execution.
- **Time Horizon:** Multi-year (3-5 Years).
- **Example:** "Become the Market Leader in Sustainable Technology."

#### 3.1.2 OKRs (Objectives and Key Results) (The "How")

OKRs are the agile engine of Tija, designed for shorter cycles and flexible alignment.

- **Objective (O):** Qualitative and inspirational (e.g., "Launch the Green Initiative").
- **Key Results (KR):** Quantitative and measurable (e.g., "Reduce server carbon footprint by 20%").
- **Bi-Directional Alignment:** Unlike Strategic Goals which flow down, OKRs in Tija will support **Bottom-Up Alignment**. A Team (L6) can propose an OKR that links _up_ to a Regional (L2) Strategic Goal. This supports the "Aligned Autonomy" model favored by modern tech enterprises.<sup>5</sup>

#### 3.1.3 KPIs (Key Performance Indicators) (The "Scoreboard")

KPIs are persistent health metrics. They do not have "start" and "end" dates in the same way projects do; they are continuous.

- **Behavior:** KPIs are often perpetual (e.g., "Maintain System Uptime > 99.9%").
- **Integration:** A KPI can be attached to a Strategic Goal as a measure of success, but it exists independently in the data model to allow for historical trending over 5+ years. This meets the requirement to "check progress... 5 years, one year, etc."

### 3.2 The Cascading Architecture (Global to Local)

The "Cascading" feature is the nervous system of Tija. We define three distinct modes of cascading that the Global Admin can select when uploading strategic objectives. This flexibility allows the organization to be rigid where necessary (compliance) and flexible where possible (innovation).<sup>5</sup>

#### Mode A: Strict Cascade (Mandatory Adoption)

- **Logic:** An L0 Goal is pushed to L1. The L1 entity _must_ accept the goal exactly as written.
- **Use Case:** Compliance goals, Safety standards, or Critical Financial Targets (e.g., "Zero Tolerance for Bribery").
- **System Action:** The system automatically creates shadow copies of the goal in the sub-entities' goal plans. These cannot be edited or deleted by the lower levels.
- **Visual Indicator:** These goals appear with a "Lock" icon and a distinct color border (e.g., Gold) to signify their global mandate.

#### Mode B: Aligned Cascade (Interpretive Adoption)

- **Logic:** L0 sets a goal. L1 is notified and must create _their own_ goal that links to the L0 goal as a "Parent."
- **Use Case:** Strategic differentiation. L0 says "Grow Revenue." L1 (Europe) interprets this as "Expand Sales Team," while L1 (Asia) interprets it as "Optimize Pricing."
- **System Action:** Creates a "To-Do" item for the Regional Manager. The system prevents the Regional Plan from being "Finalized" until all L0 Aligned Goals have a corresponding child goal linked.

#### Mode C: Hybrid/Matrix Cascade

- **Logic:** A goal is assigned based on _Functional_ lines, crossing Entity borders.
- **Use Case:** The Global CTO assigns a "Tech Debt Reduction" goal to all developers, regardless of which Country Entity pays their salary.
- **System Action:** The goal bypasses the L1-L4 administrative hierarchy and is injected directly into the goal plans of users with the "Developer" job function code, leveraging the functional reporting line.

### 3.3 Goal Properties and Attributes

To satisfy the specific requirement for "Propriety of Goals" and nuanced evaluator groups, the Goal Object schema is rich with metadata.

#### 3.3.1 Propriety and Criticality

The system distinguishes goals by a Criticality enum: Low, Medium, High, Critical.

- **Algorithm Impact:** Critical goals are not just labels; they carry functional weight. A Critical goal triggers a validation rule: it must constitute at least 20% of the total plan weight. It cannot be deleted without L+2 (Manager's Manager) approval.
- **Reporting:** The "Critical Goal Failure Report" is a specific dashboard for executives, highlighting only the red-status items marked as Critical, filtering out the noise of low-priority operational misses.

#### 3.3.2 Evaluator Groups (360-Degree Logic)

In a matrix organization, the person best suited to evaluate a goal might not be the direct supervisor. Tija implements a **Dynamic Evaluator Matrix** per goal.<sup>10</sup>

- **Configuration:** When a goal is set, the "Evaluator Group" is defined.
  - **Main Evaluator:** Usually the Functional Manager.
  - **Self:** The employee (Self-Assessment).
  - **Peer:** Cross-functional colleagues (e.g., for a "Collaboration" goal).
  - **Subordinate:** For leadership goals (e.g., "Team Morale").
- **Weighting:** The system calculates the final score for the goal based on a configured weighted average (e.g., Supervisor: 50%, Peers: 30%, Self: 20%).
- **Anonymity:** For Peer and Subordinate evaluations, the system aggregates scores and anonymizes comments to encourage honest feedback, crucial for the "Culture" aspect of the user's background.

## 4\. The Goal Library & Taxonomy System

For a global enterprise, a blank text box is the enemy of data quality. "Improve Sales" and "Increase Revenue" might mean the same thing but are impossible to aggregate if written differently. Tija utilizes a centralized **Goal Library Module** to enforce standardization while enabling customization.<sup>12</sup>

### 4.1 Taxonomy and Categorization Strategy

The library uses a faceted taxonomy to organize goals, ensuring that a manager in Brazil searching for a "Sales Goal" finds relevant, legally compliant templates.

| **Facet** | **Description** | **Examples** |
| --- | --- | --- |
| **Functional Domain** | The department or job family. | Sales, IT, HR, Legal, Operations. |
| --- | --- | --- |
| **Competency Level** | The seniority required. | Junior, Senior, Principal, Executive. |
| --- | --- | --- |
| **Strategic Pillar** | The L0 objective it supports. | Innovation, Customer Intimacy, ESG, Revenue. |
| --- | --- | --- |
| **Time Horizon** | The intended duration. | 5-Year, Annual, Quarterly, Sprint. |
| --- | --- | --- |
| **Jurisdiction Scope** | Where this goal is valid. | Global, EU-Only, Excludes-California. |
| --- | --- | --- |

### 4.2 The "Smart Template" Pattern

The Goal Library stores **Parameterized Goal Templates** rather than static text.

- **Template Structure:** "Achieve% growth in \[Product_Line\] by."
- **Instantiation:** When a user selects this from the library, the system prompts them to fill in the bracketed variables.
- **Benefits:** This structure allows the system to aggregate data later. We can report on "Average Growth Target for Product X" across all entities because the underlying data structure is consistent, even if the targets differ.

### 4.3 Versioning and Temporal Tracking

The prompt requires tracking "the date they have been set and category to check progress and the timeline."

- **System-Versioning:** Every goal record in the database utilizes "Temporal Table" design (e.g., SQL Server System-Versioning). This preserves the state of a goal at any point in time.
- **Use Case:** If an employee transfers from France to Singapore mid-year, the system creates a "snapshot" of their French goals at the moment of transfer. The final annual performance review is a weighted calculation of the "French Snapshot" (40% of the year) and the "Singapore Current State" (60% of the year). This ensures fairness and accuracy in mobile global workforces.<sup>15</sup>

## 5\. Evaluation Engine: Weighting and Scoring Logic

The requirement to aggregate performance from the individual to the global level necessitates a sophisticated mathematical engine. A simple average is insufficient due to currency differences and varying goal weights.

### 5.1 Hierarchical Weighting Algorithm

Tija uses a **Normalized Weighted Sum Model** for scoring.<sup>17</sup>

Step 1: Individual Score Calculation

For an employee \$E\$, the Total Score (\$S_E\$) is the sum of all Goal Scores (\$g_i\$) multiplied by their respective weights (\$w_i\$).

\$\$S_E = \\sum_{i=1}^{n} (g_i \\times w_i)\$\$

Constraint: \$\\sum w_i\$ must equal 100% (or 1.0). The system validates this upon plan submission.

Step 2: Section/Entity Score Calculation

The performance of a Section (L6) or Entity (L4) is not just the average of its people. It is a composite of:

- **The Aggregate People Score (\$S_{Agg}\$):** The weighted average of all subordinates.
- **The Entity Direct Score (\$S_{Dir}\$):** The score achieved on the Entity's own specific OKRs/KPIs.

\$\$Score_{Entity} = (W_{People} \\times S_{Agg}) + (W_{Direct} \\times S_{Dir})\$\$

- _Configuration:_ Typically, lower-level entities (Teams) have a higher weight on \$S_{Agg}\$ (e.g., 80%), while higher-level entities (Regions) might weight \$S_{Dir}\$ higher (e.g., 60%) to reflect their strategic focus.

### 5.2 Multi-Currency Normalization

A "Revenue Goal" of \$1M USD is numerically different from ¥100M JPY. To report on "Group Performance," Tija must normalize these values.

- **Currency Architecture:** Tija's Goal Module interfaces with the Finance Module. All monetary KPIs are stored with a CurrencyCode and a Value.
- **Standard Reporting Rate (SRR):** The system maintains a separate Exchange_Rate_Table specifically for Performance Reporting. This uses a fixed "Budget Rate" set at the beginning of the fiscal year.
- **Logic:** This prevents exchange rate volatility from unfairly affecting performance scores. If the JPY crashes against the USD, the Japanese team's performance score should not plummet if they met their local target. The SRR ensures they are evaluated on their _local_ execution, while the Global Report can toggle between "SRR View" (Execution Performance) and "Current Rate View" (Actual Financial Impact).<sup>19</sup>

## 6\. Technical Architecture: Database and Data Model

Designing the database for this system is the most critical technical challenge. A standard adjacency list (ParentID) is inefficient for the deep nesting and heavy read/write volume of a global enterprise hierarchy. We will use a **Closure Table** approach for the hierarchy and a **Star Schema** for reporting.

### 6.1 The Organizational Hierarchy Model (Closure Table)

To manage "tens of countries" and deep nesting efficiently, we avoid recursive queries which are computationally expensive. The **Closure Table Pattern** allows us to store every path from every ancestor to every descendant.<sup>20</sup>

**Table: Org_Hierarchy_Closure**

| **Ancestor_ID** | **Descendant_ID** | **Depth** | **Hierarchy_Type** |
| --- | --- | --- | --- |
| Global_Grp | Region_EMEA | 1   | Functional |
| --- | --- | --- | --- |
| Global_Grp | Entity_France | 2   | Functional |
| --- | --- | --- | --- |
| Entity_France | Team_Paris | 1   | Administrative |
| --- | --- | --- | --- |
| Global_Grp | Team_Paris | 3   | Administrative |
| --- | --- | --- | --- |

- Benefit: To generate the "Exhaustive Report" for the Global Group, we execute:
    SELECT \* FROM Goals WHERE OwnerID IN (SELECT Descendant_ID FROM Org_Hierarchy_Closure WHERE Ancestor_ID = 'Global_Grp').
    This is a single, indexed, high-speed lookup with \$O(1)\$ complexity, avoiding the \$O(n)\$ recursion of traditional trees.

### 6.2 The Goal Data Model (Core Schema)

This schema supports the polymorphism and metadata requirements.

| **Field Name** | **Type** | **Description** |
| --- | --- | --- |
| Goal_UUID | GUID | Unique Identifier (Global uniqueness). |
| --- | --- | --- |
| Parent_Goal_ID | GUID | Links to the Strategic Objective above (Alignment). |
| --- | --- | --- |
| Owner_Entity_ID | GUID | The Entity/User responsible. |
| --- | --- | --- |
| Library_Ref_ID | GUID | Link to the Goal Library template. |
| --- | --- | --- |
| Goal_Type | Enum | 'Strategic', 'OKR', 'KPI'. |
| --- | --- | --- |
| Propriety | Enum | 'Low', 'Med', 'High', 'Critical'. |
| --- | --- | --- |
| Weight | Decimal | Percentage weight (0.0-1.0). |
| --- | --- | --- |
| Progress_Metric | JSON | Stores current value, target, and logic (e.g., {"current": 80, "target": 100, "unit": "USD"}). |
| --- | --- | --- |
| Evaluator_Config | JSON | Defines the 360 matrix (e.g., {"peer_weight": 0.3, "manager_weight": 0.5}). |
| --- | --- | --- |
| Jurisdiction_ID | GUID | Links to L3 for compliance rules. |
| --- | --- | --- |
| Visibility | Enum | 'Global', 'Public', 'Private'. |
| --- | --- | --- |
| Sys_StartTime | DateTime | For temporal versioning. |
| --- | --- | --- |
| Sys_EndTime | DateTime | For temporal versioning. |
| --- | --- | --- |

### 6.3 Data Warehouse for Global Reporting

To enable the "capability of reporting on the entire organisation groups performance," the operational database (OLTP) is synced to a Data Warehouse (OLAP) using a **Star Schema**.<sup>22</sup>

- **Fact Table:** Fact_Goal_Performance
  - _Granularity:_ Weekly Snapshot per Goal.
  - _Measures:_ Current_Score, Target_Value, Actual_Value, Completion_Percentage.
- **Dimension Tables:**
  - Dim_Time: Fiscal Year, Quarter, Month.
  - Dim_Org: The flattened hierarchy (Region, Country, Entity).
  - Dim_Employee: Demographics, Tenure, Job Family.
  - Dim_Goal_Meta: Category, Source Library ID, Propriety.

This structure allows executives to slice data instantly: "Show me the completion rate of 'Critical' 'Innovation' goals in 'EMEA' for 'Q3'."

## 7\. User Experience (UX): Blueprint for Visualization

The complexity of the backend must be invisible to the user. The UX blueprint focuses on "Contextual Relevance" and "Visualizing Alignment."

### 7.1 Global Admin & Strategy View: The "Strategy Map"

For Global Managers, the interface presents a **Cascading Strategy Map**.<sup>23</sup>

- **Visual Pattern:** A Sunburst Chart or Interactive Tree. The Center Node is the L0 Strategy.
- **Interaction:**
  - Clicking the Center expands the first ring (L1 Regions).
  - Color Coding: Red/Amber/Green indicates the aggregated health of child goals within that region.
  - _Insight:_ This allows a CEO to perform "Management by Exception." They can drill down from "Global Revenue" \$\\rightarrow\$ "Region APAC" (Red) \$\\rightarrow\$ "Japan Sales Team" (Red) to identify the specific bottleneck in seconds.

### 7.2 The "Goal Wizard" (Manager/Employee View)

For the L7 Individual, the interface focuses on clarity and guidance.

- **Smart Suggestions:** When a user clicks "Add Goal," the system queries the Goal Library. It filters results based on the user's Job Family (e.g., "Developer") and the current L0 Strategic Themes.
- **The "Alignment Line":** A visual thread (connector line) appears on the screen, physically connecting the employee's new goal to their Manager's goal, and tracing the path up to the Global Strategy. This answers the "Why am I doing this?" question, increasing engagement and strategic clarity.<sup>5</sup>

### 7.3 The "Matrix Dashboard" for Project Managers

For matrix managers who evaluate people they don't administratively manage:

- **View:** "My Project Team Goals."
- **Filter:** Shows only the specific goals assigned to the Project, filtering out the employee's administrative or personal development goals.
- **Action:** Allows the Project Manager to input scores/feedback that feed directly into the employee's main review form, without needing full access to the employee's HR file.

## 8\. Compliance, Security, and Localization

In a "tens of countries" deployment, compliance is not a feature; it is a constraint.

### 8.1 Role-Based Access Control (RBAC) in a Matrix

Security must be granular. "Who can see my goals?" is a complex question in a matrix.

- **RBAC Model:**
  - Global_Admin: Full Anonymized View (can see trends, not individual names in protected regions).
  - Line_Manager: View/Edit Direct Reports (Administrative).
  - Matrix_Manager: View/Edit _Specific Goals_ assigned to them.
  - Peer_Evaluator: View _Specific Goals_ for feedback only (Blind Score View).
- **Implementation:** The permissions check queries the Employee_Assignment table. If User A has a Matrix relationship with User B, they gain explicit permissions on the relevant goal objects, overriding the default hierarchical restrictions.

### 8.2 GDPR and The "Right to be Forgotten"

- **Data Minimization:** The Goal Module stores only business-relevant data. Personal comments are stored in a separate table with stricter encryption.
- **Retention Policies:** The L3 (Jurisdiction) object defines retention rules. In Germany, performance data might need to be deleted after 3 years. The system runs a nightly job: DELETE FROM Goal_History WHERE Jurisdiction = 'DE' AND Date < NOW() - INTERVAL 3 YEAR.

## 9\. Implementation Roadmap and Change Management

Deploying Tija Goals is a transformation project.

### Phase 1: Foundation (Months 1-3)

- **Data Migration:** Ingest Org Structure into the Closure Table.
- **Library Definition:** HR Business Partners categorize and populate the Goal Library.
- **Configuration:** Set up L3 Jurisdiction rules (Weights, Compliance Flags).

### Phase 2: Strategic Pilot (Months 4-6)

- **Top-Down Test:** Global Leadership defines L0 Goals.
- **Cascade Simulation:** L1/L2 leaders "Adopt" goals. Verify the "Push" logic works.
- **Matrix Test:** Select one cross-functional project to test Matrix Evaluator permissions.

### Phase 3: Operational Rollout (Months 7-12)

- **Regional Onboarding:** L3-L6 entities onboard in waves (e.g., Wave 1: North America, Wave 2: EMEA).
- **User Training:** Launch "Goal Wizard" tutorials.

### Phase 4: Global Launch & Optimization (Year 2+)

- **AI Integration:** Activate AI analysis on the Goal Library to suggest "High Impact" goals based on Year 1 data.
- **Full Cycle Review:** First Global Performance Report generated via the Data Warehouse.

## 10\. Conclusion

The Tija Goals Module blueprint presents a sophisticated response to the challenge of global enterprise performance management. By rejecting simple hierarchies in favor of a **Dual-Matrix Architecture**, utilizing **Closure Tables** for scalable data traversing, and implementing a **Polymorphic Goal Model** that respects the distinction between rigid compliance and agile execution, Tija is positioned to become a market-leading Practice Management System.

This system does not just report on performance; it creates the structural conditions for high performance to occur. It balances the "Glocal" tension-providing the Global Headquarters with the visibility and control they need, while granting Local Entities the autonomy and cultural respect they require. This is the future of Enterprise HRMS.

# Detailed Technical Research & Specification Analysis

The following section provides the deep-dive technical justification, algorithm definitions, and schema details that underpin the executive blueprint. This serves as the reference manual for the engineering and product teams.

## 11\. Deep Dive: The Closure Table Pattern for Enterprise Hierarchies

### 11.1 The Problem with Adjacency Lists

In traditional HR systems, the organization is stored as an Adjacency List (each row has a ParentID).

- **Query:** SELECT \* FROM Org WHERE ID = 1 (Global Group).
- **Challenge:** To find _all_ descendants (down to L7 Individual) for a report, the database must perform a recursive Common Table Expression (CTE). For a 100,000-person organization with 15 levels of depth, this is slow (\$O(n)\$) and computationally expensive, especially when generating real-time dashboards.<sup>20</sup>

### 11.2 The Closure Table Solution

Tija will utilize the **Closure Table Pattern** to model the organizational graph. This involves a secondary table that stores _every_ path between _every_ node.

**Schema:**

SQL

CREATE TABLE Org_Hierarchy_Closure (
ancestor_id UUID NOT NULL,
descendant_id UUID NOT NULL,
depth INT NOT NULL,
hierarchy_type VARCHAR(20) NOT NULL -- 'Functional' or 'Administrative'
PRIMARY KEY (ancestor_id, descendant_id, hierarchy_type)
);

**Operational Efficiency:**

- **Insert Cost:** High. When adding a node, we must calculate all paths. However, org changes are _rare_ compared to reads.
- **Read Cost:** Extremely Low (\$O(1)\$). To find the entire subtree for the "EMEA Region":
    SQL
    SELECT \* FROM Goals
    JOIN Org_Hierarchy_Closure ON Goals.OwnerID = descendant_id
    WHERE ancestor_id = 'EMEA_UUID'
    AND hierarchy_type = 'Administrative';
    <br/>This query is instant, regardless of tree depth. This is critical for the requirement "reporting on the entire organisation groups performance".<sup>20</sup>

## 12\. Deep Dive: Matrix Evaluation Algorithms

The prompt requires assigning "Evaluator groups" and handling "weighting on their levels." The system must support a **Multi-Rater Weighted Average**.

### 12.1 The Scoring Algorithm

Let \$G\$ be a specific goal.

Let \$E = \\{e_1, e_2,..., e_n\\}\$ be the set of evaluators.

Let \$W = \\{w_1, w_2,..., w_n\\}\$ be the weights assigned to each evaluator's role.

Let \$S = \\{s_1, s_2,..., s_n\\}\$ be the scores given by the evaluators (normalized 0-100).

The Goal Score (\$Score_G\$) is calculated as:

\$\$Score_G = \\frac{\\sum_{i=1}^{n} (s_i \\times w_i)}{\\sum_{i=1}^{n} w_i}\$\$

Handling Missing Evaluations:

If an evaluator group (e.g., Peers) fails to submit, the system must re-normalize.

- _Scenario:_ Weights are Manager (50%), Peer (30%), Self (20%).
- _Event:_ Peer does not submit.
- _Logic:_ The system redistributes the missing 30% proportionally to the remaining groups.
  - New Manager Weight = \$50 / (50+20) = 71.4\\%\$
  - New Self Weight = \$20 / (50+20) = 28.6\\%\$
        This ensures the final score is always mathematically valid out of 100%.17

### 12.2 AHP (Analytic Hierarchy Process) for Strategic Weighting

For high-level strategic alignment (L0-L2), simple weighting is often insufficient. Executives struggle to assign arbitrary percentages. Tija will implement an **AHP Interface** for the Global Strategy setup.

- **Interface:** Instead of asking "What % weight is Innovation?", the system asks pairwise questions: "Is Innovation more important than Revenue this year?"
- **Calculation:** The system builds a comparison matrix and calculates the Eigenvector to derive the mathematical weights automatically. This ensures the "Strategy Plan" is internally consistent and reflects the true priorities of the organization.<sup>26</sup>

## 13\. Deep Dive: The Goal Library Taxonomy & AI

The Goal Library is the mechanism for scaling expertise.

### 13.1 Taxonomy Structure (SKOS Standard)

We will model the library using the **Simple Knowledge Organization System (SKOS)** standard.

- **Concepts:** Each Goal Template is a "Concept."
- **Broader/Narrower:** Templates are linked. "Increase Sales" (Broader) \$\\rightarrow\$ "Increase Recurring Revenue" (Narrower).
- **Related:** Templates are cross-linked. "Launch Product X" (Product) is related to "Market Product X" (Marketing).

### 13.2 Smart Template Schema

The Goal_Library_Item table contains the blueprint for instantiation.

JSON

{
"template_id": "SALE-001",
"name": "Increase \[Product\] Sales",
"variables":,
"default_kpis":,
"jurisdiction_deny":, // e.g. if it implies individual stack ranking
"suggested_weight": 0.25
}

This structured data allows the "Goal Wizard" to dynamically generate the UI form for the user, enforcing constraints (e.g., "You can't set a growth target > 50% without approval").<sup>12</sup>

## 14\. Deep Dive: Multi-Currency & Financial Integration

Global reporting requires normalizing disparate currencies.

### 14.1 The Dual-Rate Architecture

Tija stores two values for every monetary KPI:

- **Transactional Value:** The actual amount in local currency (e.g., 100,000 MXN). Stored with Currency_Date.
- **Reporting Value:** The converted amount in the Global Functional Currency (e.g., USD).

The "Budget Rate" Problem:

Performance management must measure effort, not market luck. If the Mexican Peso drops 20% against the USD, the Mexican Sales Manager shouldn't miss their bonus if they hit their MXN target.

- **Solution:** Tija uses a **Static Budget Rate** (fixed at the start of the fiscal year) for all Performance calculations.
- **DB Schema:**
  - Fact_KPI_Result: Local_Value, Local_Currency, Reporting_Value_Budget_Rate, Reporting_Value_Spot_Rate.
  - _Usage:_ The "Performance Dashboard" uses Reporting_Value_Budget_Rate. The "Financial Impact Dashboard" uses Reporting_Value_Spot_Rate.<sup>19</sup>

## 15\. User Interface (UI) Patterns for Cascading

Visualizing a tree of 100,000 goals requires specific UI patterns to avoid cognitive overload.

### 15.1 The "Miller Columns" Cascade Browser

To navigate the L0 \$\\rightarrow\$ L7 hierarchy, Tija uses a **Miller Columns** (Mac Finder style) layout.

- **Column 1:** L0 Strategic Themes.
- **Column 2:** L1 Regional Goals (filtered by selection in Col 1).
- **Column 3:** L4 Entity Goals.
- **Column 4:** Team/Individual Goals.
- **Benefit:** This allows a user to traverse the depth of the cascade horizontally without losing the context of the parentage.

### 15.2 The "Dependency Graph" Visualization

For the Matrix view (Hybrid Cascade), a tree is insufficient. Tija uses a **Force-Directed Graph** visualization.

- **Nodes:** Goals.
- **Edges:** Relationships (Parent/Child, Blocking, Supporting).
- **Visuals:**
  - _Thick Line:_ Strong Dependency (Parent/Child).
  - _Dotted Line:_ Matrix/Supporting connection.
  - _Color:_ Status (Red/Green).
- **Interaction:** Hovering over a node highlights all upstream and downstream dependencies, allowing a Project Manager to see if a "Marketing Delay" (Peer Goal) is blocking their "Product Launch" (Own Goal).<sup>23</sup>

## 16\. Security & Data Architecture: The Sharded Model

### 16.1 Tenant Isolation Strategy

While Tija is an "Enterprise" system, it operates internally as a **Multi-Tenant SaaS** where "Tenants" are the L3/L4 Jurisdictions.

- **Pattern:** **Database-Per-Region** (a variation of Database-per-tenant).
- **Justification:** Compliance. Russian data stays in Russia. EU data stays in EU.
- **Cross-Tenant Reporting:** This is the hardest challenge.
  - _Solution:_ **Asynchronous ETL to Anonymized Data Lake.**
  - Process: Each regional shard runs a nightly job to extract performance data.
  - Transformation: PII (Names, IDs) is hashed.
  - Load: Data is loaded into a central Global Data Warehouse (Snowflake/BigQuery).
  - Reporting: The Global Dashboard queries the Warehouse, not the Shards. This allows "Total Group Performance" reporting without violating data sovereignty, as the central view contains no un-hashed personal data.<sup>4</sup>

### 16.2 Row-Level Security (RLS)

Within a single shard (e.g., "Region Europe"), we use RLS to enforce the hierarchy.

- **Predicate:** WHERE User_ID = Current_User OR Manager_ID = Current_User OR EXISTS (SELECT 1 FROM Matrix_Link WHERE Manager_ID = Current_User).
- **Performance:** This logic is pushed to the Database Engine (e.g., PostgreSQL RLS policies) to ensure that no API bug can accidentally expose data.

## 17\. Implementation & Migration Strategy

### 17.1 Legacy Data Migration

Moving from "Spreadsheets and Emails" to Tija.

- **The "Goal Ingestion" Tool:** A specialized module that parses CSV/Excel uploads.
- **Natural Language Processing (NLP):** The ingestion tool uses NLP to map free-text legacy goals to the new Goal Library Taxonomy.
  - _Input:_ "Make more sales."
  - _AI Map:_ "Concept: Increase Revenue. Domain: Sales."
  - _Action:_ Prompts user to confirm mapping during migration.

### 17.2 Change Management: The "Goal Champions" Network

Rolling out a system this complex fails without human networks.

- **Strategy:** Identify "Goal Champions" in every L3 Jurisdiction.
- **Role:** They are "Super Users" with elevated permissions to curate the local Goal Library and approve local "Smart Templates." This ensures the system feels "owned" locally, not imposed globally.

## 18\. Future Outlook: AI & Predictive Performance

The architecture laid out in this blueprint prepares Tija for the next decade of HR Tech.

### 18.1 Predictive Intervention

With the "Star Schema" data warehouse accumulating history, Tija will eventually deploy **Predictive Models**.

- **Scenario:** The system detects a pattern: "Teams that set 'Stretch Goals' in Q1 have a 15% higher burnout/turnover rate in Q3."
- **Action:** The system proactively alerts the Manager during the Q1 Goal Setting phase: "Caution: This goal difficulty correlates with high churn. Consider adjusting."

### 18.2 Dynamic Org Design

The "Closure Table" and "Matrix Graph" data structures allow for **Organizational Network Analysis (ONA)**.

- **Insight:** Tija can identify "Hidden Leaders"-individuals who are listed as "Peer Evaluators" on a disproportionately high number of goals across the matrix.
- **Outcome:** HR can identify key influencers who are not in official management roles, leveraging the Goal Module data for Talent Management and Succession Planning.

This comprehensive technical and functional blueprint ensures that Tija is not just a recording tool, but a strategic asset that engineers alignment, drives performance, and respects the complex reality of a modern, global, multicultural enterprise.
