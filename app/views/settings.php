<!-- SETTINGS TAB -->
<div id="view-settings" class="settings-view <?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'hidden' : ''; ?>">
	<div class="settings-view__container">
		
		<!-- Sidebar -->
		<div class="settings-sidebar">
			<div class="settings-sidebar__header">
				<span class="settings-sidebar__title">Repositories</span>
				<button onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="settings-sidebar__add-btn"><i class="fa-solid fa-plus"></i> Add</button>
			</div>
			<div class="settings-sidebar__nav">
				<nav class="settings-sidebar__nav-list">
					<?php if (!empty($glob['repositories'])): ?>
						<?php foreach ($glob['repositories'] as $repo): ?>
							<a href="<?php echo BASEURL; ?>settings/<?php echo $repo['id']; ?>" class="settings-sidebar__nav-item <?php echo (isset($glob['selected_repo']) && $glob['selected_repo']['id'] === $repo['id']) ? 'settings-sidebar__nav-item--active' : 'settings-sidebar__nav-item--inactive'; ?>">
								<i class="fa-brands fa-github settings-sidebar__nav-icon"></i>
								<span class="settings-sidebar__nav-text"><?php echo htmlspecialchars($repo['full_name']); ?></span>
							</a>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="settings-sidebar__empty">
							No repositories yet
						</div>
					<?php endif; ?>
				</nav>
			</div>
		</div>

		<!-- Content -->
		<div class="settings-content <?php echo (empty($glob['repositories']) || !isset($glob['selected_repo'])) ? 'settings-content--centered' : ''; ?>">
			<?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
			<div class="settings-content__wrapper">
					<!-- Repo selected - show header -->
					<div class="settings-header">
						<div class="settings-header__info">
							<div class="settings-header__title-row">
								<h2 class="settings-header__title"><?php echo htmlspecialchars($glob['selected_repo']['full_name']); ?></h2>
								<?php if (isset($glob['user']) && $glob['user']): ?>
									<div class="connection-badge connection-badge--connected">
										<span class="connection-badge__indicator connection-badge__indicator--connected"></span>
										Connected
									</div>
								<?php else: ?>
									<div class="connection-badge connection-badge--disconnected">
										<span class="connection-badge__indicator connection-badge__indicator--disconnected"></span>
										Disconnected
									</div>
								<?php endif; ?>
							</div>
							<p class="settings-header__description">Manage how Groundskeeper interacts with this repo.</p>
						</div>
						<div class="settings-header__actions">
							<a href="<?php echo BASEURL; ?>reset/<?php echo $glob['selected_repo']['id']; ?>" class="settings-header__action-link settings-header__action-link--reset">
								<i class="fa-solid fa-rotate-left"></i> Reset Data
							</a>
							<form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/delete" onsubmit="return confirm('Are you sure you want to remove this repository?');">
								<button type="submit" class="settings-header__action-btn settings-header__action-btn--delete">
									<i class="fa-solid fa-trash"></i> Remove
								</button>
							</form>
						</div>
					</div>
				<?php else: ?>
					<!-- No repos - show blank slate -->
					<div class="settings-blank-slate">
						<div class="settings-blank-slate__icon">
							<i class="fa-brands fa-github"></i>
						</div>
						<h3 class="settings-blank-slate__title">No repositories connected</h3>
						<p class="settings-blank-slate__description">Add your first repository to start analyzing issues.</p>
						<button onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="settings-blank-slate__button">
							<i class="fa-solid fa-plus"></i>
							Add Your First Repository
						</button>
					</div>
				<?php endif; ?>

				<?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
				<!-- Config Form -->
				<div class="settings-sections">
					<!-- GitHub Connection Section -->
					<div class="github-connection-section">
						<h3 class="github-connection-section__title">GitHub Connection</h3>
						<p class="github-connection-section__description">View and analyze issues using your GitHub username and optional Personal Access Token.</p>

						<?php if (isset($glob['user']) && $glob['user']): ?>
							<!-- Connected state -->
							<div class="github-connection-section__user">
								<?php if (!empty($glob['user']['avatar_url'])): ?>
									<img src="<?php echo htmlspecialchars($glob['user']['avatar_url']); ?>" alt="Avatar" class="github-connection-section__avatar">
								<?php endif; ?>
								<div class="github-connection-section__user-info">
									<p class="github-connection-section__user-name">Connected as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
									<p class="github-connection-section__access-mode"><i class="fa-solid fa-eye"></i> Read-only access</p>
								</div>
							</div>
							<div class="github-connection-section__actions">
								<button onclick="document.getElementById('pat-form').classList.toggle('hidden')" class="github-connection-section__button github-connection-section__button--update">Update Token</button>
								<a href="<?php echo BASEURL; ?>settings/disconnect" class="github-connection-section__button github-connection-section__button--disconnect">Disconnect</a>
							</div>

							<!-- PAT Update Form (hidden by default) -->
							<div id="pat-form" class="github-connection-section__pat-form hidden">
								<form method="POST" action="<?php echo BASEURL; ?>settings/update-token">
									<div class="settings-form-field">
										<label class="settings-form-field__label">Personal Access Token</label>
										<input type="text" name="personal_access_token" class="settings-form-field__input" placeholder="ghp_xxxxxxxxxxxx">
										<p class="settings-form-field__help">Update your PAT to increase rate limits (5000 req/hr)</p>
									</div>
									<button type="submit" class="settings-form-field__submit">Update Token</button>
								</form>
							</div>
						<?php else: ?>
							<!-- Not connected state -->
							<form method="POST" action="<?php echo BASEURL; ?>settings/connect-readonly" class="github-connection-section__connect-form">
								<div class="settings-form-field">
									<label class="settings-form-field__label">GitHub Username</label>
									<input type="text" name="github_username" required class="settings-form-field__input" placeholder="your-github-username">
								</div>
								<div class="settings-form-field">
									<label class="settings-form-field__label">Personal Access Token (optional)</label>
									<input type="text" name="personal_access_token" class="settings-form-field__input" placeholder="ghp_xxxxxxxxxxxx">
									<p class="settings-form-field__help">Without token: 60 requests/hour. With token: 5000 requests/hour. <a href="https://github.com/settings/tokens/new?scopes=public_repo&description=Groundskeeper" target="_blank">Create token</a></p>
								</div>
								<button type="submit" class="github-connection-section__submit-btn">
									<i class="fa-brands fa-github"></i>
									Connect GitHub
								</button>
							</form>
						<?php endif; ?>
					</div>

					<?php if ($glob['selected_repo']['last_synced_at']): ?>
					<!-- Labels Section -->
					<div class="label-mapping-section">
						<h3 class="label-mapping-section__title">Label Mapping</h3>

						<form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/update" class="label-mapping-section__form">
							<div>
								<label class="label-mapping-section__field-title">Bug Label</label>
								<p class="label-mapping-section__field-description">Which label indicates an issue is a bug?</p>
								<input type="text" name="bug_label" value="<?php echo htmlspecialchars($glob['selected_repo']['bug_label']); ?>" class="settings-form-field__input" placeholder="bug">
								<p class="settings-form-field__help">Examples: bug, type: bug, defect</p>
							</div>

							<div>
								<label class="label-mapping-section__field-title">Priority Labels</label>
								<p class="label-mapping-section__field-description">Enter labels used to denote priority levels, one per line.</p>

								<?php
								$priorityLabelsText = '';
								if (!empty($glob['selected_repo']['priority_labels'])) {
									$priorityLabels = json_decode($glob['selected_repo']['priority_labels'], true);
									if (is_array($priorityLabels)) {
										$priorityLabelsText = implode("\n", $priorityLabels);
									}
								}
								?>

								<textarea name="priority_labels_text" rows="4" class="settings-form-field__textarea" placeholder="priority: high&#10;priority: medium&#10;priority: low"><?php echo htmlspecialchars($priorityLabelsText); ?></textarea>
								<p class="settings-form-field__help">Leave blank if this repository doesn't use priority labels.</p>
							</div>

							<!-- Areas Section -->
							<div class="areas-section">
								<label class="areas-section__title">Functional Areas</label>
								<p class="areas-section__description">Areas are auto-detected on first analysis using AI. They help categorize issues by codebase section.</p>

								<?php if (!empty($glob['areas'])): ?>
									<div class="areas-section__list">
										<ul>
											<?php foreach ($glob['areas'] as $area): ?>
												<li>
													<i class="fa-solid fa-circle areas-section__list-icon"></i>
													<?php echo htmlspecialchars($area['name']); ?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
									<form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/reset-areas" class="inline">
										<button type="submit" class="areas-section__reset-btn" onclick="return confirm('Are you sure you want to reset areas? This will clear all area categorizations and re-discover areas on next analysis.')">
											<i class="fa-solid fa-rotate-left"></i> Reset Areas
										</button>
									</form>
								<?php else: ?>
									<div class="areas-section__empty">
										<i class="fa-solid fa-info-circle"></i>
										No areas detected yet. Run analysis to discover areas automatically.
									</div>
								<?php endif; ?>
							</div>

							<div class="label-mapping-section__footer">
								<button type="submit" class="label-mapping-section__save-btn">Save Changes</button>
							</div>
						</form>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

