document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("createPlan-form");

  // Initialize date inputs
  initializeDates();

  // Setup AI Generate button
  setupAIGeneration();

  // Form submission validation
  if (form) {
    form.addEventListener("submit", function (event) {
      clearErrors();
      let valid = validateForm();
      if (!valid) {
        event.preventDefault();
      }
    });
  }
});

// ==================== AI Plan Generation ====================

function setupAIGeneration() {
  const generateBtn = document.getElementById("generatePlanBtn");
  if (generateBtn) {
    generateBtn.addEventListener("click", generatePlanFromAI);
  }
}

async function generatePlanFromAI() {
  const promptInput = document.getElementById("aiPrompt");
  const prompt = promptInput.value.trim();

  if (!prompt) {
    alert("Please describe the recovery plan you want to create.");
    return;
  }

  const generateBtn = document.getElementById("generatePlanBtn");
  const originalText = generateBtn.innerHTML;
  generateBtn.innerHTML = "Generating...";
  generateBtn.disabled = true;

  try {
    // Few-shot prompting: Generate plan based on description
    const planData = await generatePlanWithFewShot(prompt);

    // Fill in the form with generated data
    fillFormWithPlan(planData);

    // Show success message
    promptInput.value = "";
    showNotification(
      "Plan generated successfully! Review and adjust as needed.",
      "success"
    );
  } catch (error) {
    console.error("Error generating plan:", error);
    showNotification("Failed to generate plan. Please try again.", "error");
  } finally {
    generateBtn.innerHTML = originalText;
    generateBtn.disabled = false;
  }
}

function generatePlanWithFewShot(userPrompt) {
  // Simulated AI response using few-shot pattern matching
  // In production, this would call your backend AI endpoint

  return new Promise((resolve) => {
    setTimeout(() => {
      // Parse the prompt for keywords
      const lowerPrompt = userPrompt.toLowerCase();

      // Detect addiction type
      let addictionType = "substance";
      if (lowerPrompt.includes("alcohol")) addictionType = "alcohol";
      else if (lowerPrompt.includes("drug") || lowerPrompt.includes("opioid"))
        addictionType = "drug";
      else if (lowerPrompt.includes("gambling")) addictionType = "gambling";
      else if (
        lowerPrompt.includes("smoking") ||
        lowerPrompt.includes("nicotine")
      )
        addictionType = "nicotine";

      // Detect duration
      let duration = 3; // default 3 months
      const monthMatch = lowerPrompt.match(/(\d+)\s*month/);
      if (monthMatch) duration = parseInt(monthMatch[1]);

      // Few-shot template based on addiction type
      const templates = {
        alcohol: {
          title: "Alcohol Recovery Program",
          goal: "Achieve and maintain sobriety through structured support and coping strategies",
          description:
            "A comprehensive alcohol recovery plan focusing on detoxification, behavioral therapy, and lifestyle changes to support long-term sobriety.",
          phases: {
            1: {
              tasks: [
                "Medical evaluation and detox",
                "Identify triggers",
                "Join support group",
              ],
              milestones: ["Complete detox safely"],
            },
            2: {
              tasks: [
                "Weekly therapy sessions",
                "Develop coping strategies",
                "Exercise routine",
              ],
              milestones: ["30 days sober"],
            },
            3: {
              tasks: [
                "Relapse prevention planning",
                "Build support network",
                "Set future goals",
              ],
              milestones: ["Complete program"],
            },
          },
        },
        drug: {
          title: "Drug Addiction Recovery Program",
          goal: "Overcome substance dependency through medical support and behavioral therapy",
          description:
            "A structured approach to drug addiction recovery including medically supervised detox, counseling, and ongoing support systems.",
          phases: {
            1: {
              tasks: [
                "Medical assessment",
                "Supervised detoxification",
                "Initial counseling",
              ],
              milestones: ["Complete detox"],
            },
            2: {
              tasks: [
                "Individual therapy",
                "Group therapy sessions",
                "Family counseling",
              ],
              milestones: ["60 days clean"],
            },
            3: {
              tasks: [
                "Aftercare planning",
                "Career counseling",
                "Community integration",
              ],
              milestones: ["6 months recovery"],
            },
          },
        },
        gambling: {
          title: "Gambling Addiction Recovery Plan",
          goal: "Develop healthy financial habits and overcome compulsive gambling behavior",
          description:
            "A recovery plan addressing the psychological and financial aspects of gambling addiction.",
          phases: {
            1: {
              tasks: [
                "Financial audit",
                "Install blocking software",
                "Identify triggers",
              ],
              milestones: ["One week gamble-free"],
            },
            2: {
              tasks: [
                "CBT therapy sessions",
                "Attend GA meetings",
                "Budget planning",
              ],
              milestones: ["30 days gamble-free"],
            },
            3: {
              tasks: [
                "Debt repayment plan",
                "New hobbies",
                "Long-term financial goals",
              ],
              milestones: ["90 days gamble-free"],
            },
          },
        },
        nicotine: {
          title: "Nicotine Cessation Program",
          goal: "Successfully quit smoking/vaping and maintain a smoke-free lifestyle",
          description:
            "A step-by-step plan to quit nicotine including NRT options, behavioral changes, and relapse prevention.",
          phases: {
            1: {
              tasks: [
                "Set quit date",
                "Start NRT if needed",
                "Identify triggers",
              ],
              milestones: ["Quit date reached"],
            },
            2: {
              tasks: [
                "Daily check-ins",
                "Exercise routine",
                "Stress management",
              ],
              milestones: ["2 weeks smoke-free"],
            },
            3: {
              tasks: [
                "Reduce NRT gradually",
                "Celebrate milestones",
                "Plan for long-term",
              ],
              milestones: ["30 days smoke-free"],
            },
          },
        },
        substance: {
          title: "General Addiction Recovery Plan",
          goal: "Achieve sustained recovery through personalized treatment and support",
          description:
            "A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.",
          phases: {
            1: {
              tasks: [
                "Initial assessment",
                "Goal setting",
                "Build support system",
              ],
              milestones: ["Complete assessment"],
            },
            2: {
              tasks: [
                "Regular therapy",
                "Develop coping skills",
                "Healthy routines",
              ],
              milestones: ["30 days progress"],
            },
            3: {
              tasks: [
                "Relapse prevention",
                "Life skills training",
                "Future planning",
              ],
              milestones: ["Complete program"],
            },
          },
        },
      };

      const template = templates[addictionType];

      // Calculate dates
      const startDate = new Date();
      const endDate = new Date();
      endDate.setMonth(endDate.getMonth() + duration);

      resolve({
        title: template.title,
        goal: template.goal,
        description: template.description,
        startDate: formatDate(startDate),
        endDate: formatDate(endDate),
        phases: template.phases,
        notes: `Generated plan for ${addictionType} addiction recovery. Duration: ${duration} months. Please customize based on client's specific needs.`,
      });
    }, 1500); // Simulate API delay
  });
}

function fillFormWithPlan(planData) {
  // Fill basic fields
  document.getElementById("title").value = planData.title || "";
  document.getElementById("planGoal").value = planData.goal || "";
  document.getElementById("description").value = planData.description || "";
  document.getElementById("startDate").value = planData.startDate || "";
  document.getElementById("endDate").value = planData.endDate || "";
  document.getElementById("notes").value = planData.notes || "";

  // Fill phases with tasks
  if (planData.phases) {
    for (let phaseNum = 1; phaseNum <= 3; phaseNum++) {
      const phaseData = planData.phases[phaseNum];
      if (phaseData) {
        const container = document.getElementById(`phase-${phaseNum}-tasks`);
        if (container) {
          container.innerHTML = ""; // Clear existing

          // Add tasks
          if (phaseData.tasks) {
            phaseData.tasks.forEach((task) => {
              addTaskWithValue(phaseNum, task);
            });
          }

          // Add milestones
          if (phaseData.milestones) {
            phaseData.milestones.forEach((milestone) => {
              addMilestoneWithValue(phaseNum, milestone);
            });
          }
        }
      }
    }
  }
}

// ==================== Task & Milestone Management ====================

function addTask(phaseNum) {
  addTaskWithValue(phaseNum, "");
}

function addTaskWithValue(phaseNum, value) {
  const container = document.getElementById(`phase-${phaseNum}-tasks`);
  if (!container) return;

  const taskHTML = `
        <div class="task-item">
            <input type="hidden" name="taskPhase[]" value="${phaseNum}">
            <input type="text" name="taskTitle[]" value="${value}" placeholder="Enter task description">
            <select name="taskType[]" style="width: auto;">
                <option value="custom">Custom</option>
                <option value="journal">Journal</option>
                <option value="meditation">Meditation</option>
                <option value="session">Session</option>
                <option value="exercise">Exercise</option>
            </select>
            <select name="recurrencePattern[]" style="width: auto;">
                <option value="">One-time</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="bi-weekly">Bi-weekly</option>
            </select>
            <span class="remove-btn" onclick="this.parentElement.remove()">×</span>
        </div>`;
  container.insertAdjacentHTML("beforeend", taskHTML);
}

function addMilestone(phaseNum) {
  addMilestoneWithValue(phaseNum, "");
}

function addMilestoneWithValue(phaseNum, value) {
  const container = document.getElementById(`phase-${phaseNum}-tasks`);
  if (!container) return;

  const milestoneHTML = `
        <div class="milestone-item">
            <span style="color: var(--color-primary);">⭐</span>
            <input type="text" name="phase${phaseNum}Milestone[]" value="${value}" placeholder="Enter milestone">
            <span class="remove-btn" onclick="this.parentElement.remove()">×</span>
        </div>`;
  container.insertAdjacentHTML("beforeend", milestoneHTML);
}

// ==================== Client Selection ====================

function showClientDropdown() {
  const select = document.getElementById("assignedTo");
  const button = document.querySelector(".add-client-btn");

  if (select) {
    select.style.display = "block";
    button.style.display = "none";

    select.addEventListener(
      "change",
      function () {
        if (this.value) {
          const selectedText = this.options[this.selectedIndex].text;
          const selectedClient = document.getElementById("selectedClient");
          selectedClient.innerHTML = `<span>${selectedText}</span> <span class="remove-btn" onclick="removeClient()">×</span>`;
          selectedClient.style.display = "flex";
          select.style.display = "none";
        }
      },
      { once: true }
    );
  }
}

function removeClient() {
  const select = document.getElementById("assignedTo");
  const selectedClient = document.getElementById("selectedClient");
  const button = document.querySelector(".add-client-btn");

  select.value = "";
  selectedClient.style.display = "none";
  button.style.display = "inline-flex";
}

// ==================== Form Validation ====================

function validateForm() {
  let valid = true;

  const title = document.getElementById("title");
  const startDate = document.getElementById("startDate");
  const endDate = document.getElementById("endDate");
  const description = document.getElementById("description");

  if (!title.value.trim()) {
    showError(title, "Plan title is required.");
    valid = false;
  }

  if (!startDate.value) {
    showError(startDate, "Start date is required.");
    valid = false;
  }

  if (!endDate.value) {
    showError(endDate, "End date is required.");
    valid = false;
  }

  if (
    startDate.value &&
    endDate.value &&
    new Date(startDate.value) > new Date(endDate.value)
  ) {
    showError(endDate, "End date must be after start date.");
    valid = false;
  }

  return valid;
}

function showError(input, message) {
  input.classList.add("error-border");
  if (input.parentNode.querySelector(".error-message")) return;

  const error = document.createElement("small");
  error.className = "error-message";
  error.textContent = message;
  input.parentNode.appendChild(error);
}

function clearErrors() {
  document.querySelectorAll(".error-message").forEach((el) => el.remove());
  document
    .querySelectorAll(".error-border")
    .forEach((el) => el.classList.remove("error-border"));
}

// ==================== Utility Functions ====================

function initializeDates() {
  const startDate = document.getElementById("startDate");
  const endDate = document.getElementById("endDate");

  if (startDate && !startDate.value) {
    startDate.value = formatDate(new Date());
  }

  if (endDate && !endDate.value) {
    const threeMonthsLater = new Date();
    threeMonthsLater.setMonth(threeMonthsLater.getMonth() + 3);
    endDate.value = formatDate(threeMonthsLater);
  }
}

function formatDate(date) {
  return date.toISOString().split("T")[0];
}

function showNotification(message, type) {
  // Simple notification
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        background: ${type === "success" ? "#4CAF50" : "#f44336"};
        color: white;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 4000);
}

function exportPDF() {
  alert("PDF export functionality will be implemented with a backend service.");
}
