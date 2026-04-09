class DashboardManager {
  constructor() {
    this.taskCheckboxes = [];
    this.progressData = {
      daysSober: 35,
      milestoneProgress: 30,
      overallProgress: 75,
    };

    this.init();
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.setupEventListeners();
      this.initializeComponents();
      this.loadUserData();
    });
  }

  setupEventListeners() {
    // Task checkbox interactions
    this.setupTaskCheckboxes();

    // Quick log form submission
    this.setupQuickLogForm();

    // Progress tracker click
    this.setupProgressTracker();

    // Emergency help button
    this.setupEmergencyHelp();

    // Sidebar navigation
    this.setupSidebarNavigation();
  }

  setupTaskCheckboxes() {
    const taskCheckboxes = document.querySelectorAll(".task-checkbox");

    taskCheckboxes.forEach((checkbox, index) => {
      checkbox.addEventListener("click", (e) => {
        this.toggleTask(e.target, index);
      });
    });
  }

  toggleTask(checkbox, index) {
    const isCompleted = checkbox.classList.contains("completed");

    if (isCompleted) {
      checkbox.classList.remove("completed");
    } else {
      checkbox.classList.add("completed");
      this.showTaskCompletionFeedback(checkbox);
    }

    // Update progress
    this.updateTaskProgress();

    // Save to server
    this.saveTaskStatus(index, !isCompleted);
  }

  showTaskCompletionFeedback(checkbox) {
    // Add a subtle animation or feedback
    checkbox.style.transform = "scale(1.1)";
    setTimeout(() => {
      checkbox.style.transform = "scale(1)";
    }, 150);
  }

  updateTaskProgress() {
    const completedTasks = document.querySelectorAll(
      ".task-checkbox.completed",
    ).length;
    const totalTasks = document.querySelectorAll(".task-checkbox").length;
    const progressPercentage = Math.round((completedTasks / totalTasks) * 100);

    // Update milestone progress
    this.progressData.milestoneProgress = progressPercentage;
    this.updateProgressDisplay();
  }

  updateProgressDisplay() {
    // Update milestone progress bar
    const progressBar = document.querySelector(".progress");
    const progressText = document.querySelector(
      ".milestone-progress-card span",
    );

    if (progressBar && progressText) {
      progressBar.style.setProperty(
        "--value",
        `${this.progressData.milestoneProgress}%`,
      );
      progressText.textContent = `${this.progressData.milestoneProgress}%`;
    }

    // Update overall progress circle
    const progressCircle = document.querySelector(".progress-circle");
    const progressPercentage = document.querySelector(".progress-percentage");

    if (progressCircle && progressPercentage) {
      progressCircle.style.setProperty(
        "--progress",
        this.progressData.overallProgress,
      );
      progressPercentage.textContent = `${this.progressData.overallProgress}%`;
    }
  }

  setupQuickLogForm() {
    const quickLogForm = document.querySelector(".quick-log-content form");
    const textarea = document.querySelector(".log-textarea");
    const submitBtn = document.querySelector(".log-submit-btn");

    if (!quickLogForm) return;

    // Auto-resize textarea
    textarea?.addEventListener("input", (e) => {
      this.autoResizeTextarea(e.target);
    });

    // Form submission
    quickLogForm.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleQuickLogSubmit(e);
    });
  }

  autoResizeTextarea(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = Math.max(60, textarea.scrollHeight) + "px";
  }

  handleQuickLogSubmit(e) {
    const formData = new FormData(e.target);
    const logEntry = formData.get("logEntry");
    const submitBtn = e.target.querySelector(".log-submit-btn");

    if (!logEntry || logEntry.trim().length === 0) {
      this.showMessage("Please write something in your log.", "error");
      return;
    }

    // Show loading state
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Saving...";

    // Submit to server
    this.saveQuickLog(formData)
      .then((response) => {
        if (response.success) {
          this.showMessage("Log saved successfully!", "success");
          e.target.reset();
        } else {
          this.showMessage(response.message || "Failed to save log.", "error");
        }
      })
      .catch((error) => {
        console.error("Error saving log:", error);
        this.showMessage("An error occurred while saving.", "error");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
  }

  setupProgressTracker() {
    const progressTracker = document.querySelector(".progress-tracker-card");

    progressTracker?.addEventListener("click", () => {
      // Navigate to detailed progress page
      window.location.href = "/user/progress-tracker";
    });
  }

  setupEmergencyHelp() {
    const emergencyBtn = document.querySelector(
      ".emergency-banner .btn-primary",
    );

    emergencyBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      this.handleEmergencyClick();
    });
  }

  handleEmergencyClick() {
    // Show confirmation dialog
    const confirmed = confirm(
      "Are you experiencing a crisis? This will connect you to emergency resources.",
    );

    if (confirmed) {
      // Navigate to emergency page or show emergency modal
      window.location.href = "/user/help";
    }
  }

  setupSidebarNavigation() {
    const sidebarLinks = document.querySelectorAll(".sidebar-nav-link");

    sidebarLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        // Add loading state for navigation
        this.showNavigationLoading(e.target.closest(".sidebar-item"));
      });
    });
  }

  showNavigationLoading(sidebarItem) {
    const originalContent = sidebarItem.innerHTML;
    const loadingSpinner = '<div class="loading-spinner"></div>';

    // Add loading class
    sidebarItem.classList.add("loading");

    // Restore after navigation (in case it fails)
    setTimeout(() => {
      sidebarItem.classList.remove("loading");
    }, 2000);
  }

  initializeComponents() {
    // Initialize progress displays
    this.updateProgressDisplay();

    // Load dynamic content
    this.loadCommunityHighlights();
    this.loadUpcomingSessions();
    this.loadAchievements();
    this.loadMotivationalQuote();
  }

  loadUserData() {
    // Load user-specific data from server
    if (window.serverData) {
      this.progressData = {
        daysSober: window.serverData.daysSober || 35,
        milestoneProgress: window.serverData.milestoneProgress || 30,
        overallProgress: window.serverData.overallProgress || 75,
      };

      this.updateProgressDisplay();
      this.updateDaysSober();
    }
  }

  updateDaysSober() {
    const daysSoberElement = document.querySelector(".days-sober-card h2");
    if (daysSoberElement) {
      daysSoberElement.textContent = this.progressData.daysSober;
    }
  }

  loadCommunityHighlights() {
    // This would typically fetch from an API
    // For now, we'll just add some dynamic behavior
    const highlights = document.querySelectorAll(".highlight-item");

    highlights.forEach((highlight) => {
      highlight.addEventListener("click", () => {
        // Navigate to community or user profile
        window.location.href = "/user/community";
      });
    });
  }

  loadUpcomingSessions() {
    const sessionInfo = document.querySelector(".session-info");

    sessionInfo?.addEventListener("click", () => {
      // Navigate to sessions page
      window.location.href = "/user/sessions";
    });
  }

  loadAchievements() {
    const achievementBadge = document.querySelector(".achievement-badge");

    achievementBadge?.addEventListener("click", () => {
      // Show achievement details or navigate to achievements page
      this.showAchievementDetails();
    });
  }

  showAchievementDetails() {
    // Create and show achievement modal or navigate to achievements page
    alert(
      "Achievement: 7 Days Strong - You've maintained sobriety for a full week!",
    );
  }

  loadMotivationalQuote() {
    const quotes = [
      "You are stronger than you think.",
      "Recovery is not a destination, it's a journey.",
      "One day at a time, one step at a time.",
      "Your journey matters, and so do you.",
      "Progress, not perfection.",
      "You have the power to change your story.",
    ];

    // Randomly select and update quote
    const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
    const quoteText = document.querySelector(".quote-text p");

    if (quoteText && Math.random() > 0.7) {
      // 30% chance to update quote
      quoteText.textContent = `"${randomQuote}"`;
    }
  }

  // API Methods
  async saveTaskStatus(taskIndex, completed) {
    try {
      const response = await fetch("/user/dashboard/save-task", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          taskIndex: taskIndex,
          completed: completed,
        }),
      });

      return await response.json();
    } catch (error) {
      console.error("Error saving task status:", error);
      return { success: false, message: "Failed to save task status" };
    }
  }

  async saveQuickLog(formData) {
    try {
      const response = await fetch("/user/quick-log", {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (response.ok) {
        return await response.json();
      } else {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
    } catch (error) {
      console.error("Error saving quick log:", error);
      throw error;
    }
  }

  // Utility Methods
  showMessage(message, type = "info") {
    // Remove existing messages
    const existingMessage = document.querySelector(".dashboard-message");
    if (existingMessage) {
      existingMessage.remove();
    }

    // Create message element
    const messageDiv = document.createElement("div");
    messageDiv.className = `dashboard-message dashboard-message-${type}`;
    messageDiv.textContent = message;

    // Style the message
    messageDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      z-index: 1000;
      animation: slideInRight 0.3s ease;
      ${
        type === "error"
          ? "background: #fee; color: #c53030; border: 1px solid #feb2b2;"
          : type === "success"
            ? "background: #f0fff4; color: #22543d; border: 1px solid #9ae6b4;"
            : "background: #ebf8ff; color: #2b6cb0; border: 1px solid #90cdf4;"
      }
    `;

    document.body.appendChild(messageDiv);

    // Auto-remove after 4 seconds
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.remove();
      }
    }, 4000);
  }
}

// Initialize dashboard when DOM is loaded
new DashboardManager();
