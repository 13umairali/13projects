import UIKit
import UserNotifications
import UserNotificationsUI
import ImageIO

/// iOS owns the collapsed banner and native actions. This card replaces only
/// the expanded content, for both local notifications and APNs alerts.
final class NotificationViewController: UIViewController, UNNotificationContentExtension {
  private let card = UIStackView()
  private let sender = UILabel()
  private let message = UILabel()
  private let time = UILabel()
  private let avatar = UIImageView()
  private let initial = UILabel()
  private var avatarTask: URLSessionDownloadTask?
  private var representedNotification = UUID()

  override func loadView() {
    view = UIView()
    view.backgroundColor = .systemBackground
    // iOS already provides the Dahimail icon/name above expanded content.
    // Keep one brand identity and give the sender/message the available width.
    sender.numberOfLines = 2
    sender.textColor = .label
    message.numberOfLines = 8
    message.textColor = .secondaryLabel
    time.textColor = .secondaryLabel
    time.setContentCompressionResistancePriority(.required, for: .horizontal)
    time.widthAnchor.constraint(lessThanOrEqualToConstant: 58).isActive = true
    let heading = UIStackView(arrangedSubviews: [sender, time])
    heading.alignment = .top
    heading.spacing = 8
    let content = UIStackView(arrangedSubviews: [heading, message])
    content.axis = .vertical
    content.spacing = 5
    avatar.contentMode = .scaleAspectFill
    avatar.clipsToBounds = true
    avatar.layer.cornerRadius = 20
    avatar.widthAnchor.constraint(equalToConstant: 40).isActive = true
    avatar.heightAnchor.constraint(equalToConstant: 40).isActive = true
    initial.textAlignment = .center
    initial.textColor = .white
    initial.font = .systemFont(ofSize: 14, weight: .semibold)
    initial.translatesAutoresizingMaskIntoConstraints = false
    avatar.addSubview(initial)
    NSLayoutConstraint.activate([
      initial.leadingAnchor.constraint(equalTo: avatar.leadingAnchor),
      initial.trailingAnchor.constraint(equalTo: avatar.trailingAnchor),
      initial.topAnchor.constraint(equalTo: avatar.topAnchor),
      initial.bottomAnchor.constraint(equalTo: avatar.bottomAnchor)
    ])
    card.axis = .horizontal
    card.alignment = .top
    card.spacing = 8
    for child in [content, avatar] { card.addArrangedSubview(child) }
    card.translatesAutoresizingMaskIntoConstraints = false
    view.addSubview(card)
    NSLayoutConstraint.activate([
      card.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 16),
      card.trailingAnchor.constraint(equalTo: view.trailingAnchor, constant: -16),
      card.topAnchor.constraint(equalTo: view.topAnchor, constant: 10)
    ])
    updateFonts()
  }

  private func updateFonts() {
    sender.font = UIFontMetrics(forTextStyle: .subheadline).scaledFont(for: .systemFont(ofSize: 16, weight: .semibold))
    message.font = UIFontMetrics(forTextStyle: .subheadline).scaledFont(for: .systemFont(ofSize: 15))
    time.font = UIFontMetrics(forTextStyle: .caption2).scaledFont(for: .systemFont(ofSize: 10))
    for label in [sender, message, time] { label.adjustsFontForContentSizeCategory = true }
  }

  override func viewDidLayoutSubviews() {
    super.viewDidLayoutSubviews()
    guard view.bounds.width > 32 else { return }
    let size = card.systemLayoutSizeFitting(
      CGSize(width: view.bounds.width - 32, height: UIView.layoutFittingCompressedSize.height),
      withHorizontalFittingPriority: .required, verticalFittingPriority: .fittingSizeLevel)
    let height = ceil(size.height) + 20
    if abs(preferredContentSize.height - height) > 0.5 {
      preferredContentSize = CGSize(width: view.bounds.width, height: height)
    }
  }

  func didReceive(_ notification: UNNotification) {
    loadViewIfNeeded()
    avatarTask?.cancel()
    let token = UUID()
    representedNotification = token
    let content = notification.request.content
    var data = content.userInfo
    // flutter_local_notifications stores the original envelope as JSON.
    if let raw = data["payload"] as? String, let bytes = raw.data(using: .utf8),
       let payload = try? JSONSerialization.jsonObject(with: bytes) as? [String: Any] {
      for (key, value) in payload { data[key] = value }
    }
    func text(_ key: String) -> String {
      (data[key] as? String ?? "").trimmingCharacters(in: .whitespacesAndNewlines)
    }
    let name = [text("sender_name"), text("caller_name"), text("from_name"), content.title, "Dahimail"]
      .first { !$0.isEmpty } ?? "Dahimail"
    let type = text("type").lowercased()
    sender.text = name
    message.text = type == "friend_request" ? "Sent you a friend request" :
      type == "friend_accepted" ? "Accepted your friend request" : content.body
    time.text = relativeTime(data, delivered: notification.date)
    avatar.image = nil
    avatar.backgroundColor = avatarColor(name)
    initial.text = String(name.trimmingCharacters(in: .whitespacesAndNewlines).prefix(1)).uppercased()
    initial.isHidden = false
    view.setNeedsLayout()
    let avatarURL = [text("sender_avatar"), text("caller_avatar"), text("from_avatar")].first { !$0.isEmpty } ?? ""
    guard let url = URL(string: avatarURL), url.scheme?.lowercased() == "https" else { return }
    var request = URLRequest(url: url)
    request.timeoutInterval = 5
    // Download to disk and downsample before decoding to bound image memory.
    avatarTask = URLSession.shared.downloadTask(with: request) { [weak self] file, response, _ in
      guard let file = file, let response = response as? HTTPURLResponse,
            (200..<300).contains(response.statusCode),
            let values = try? file.resourceValues(forKeys: [.fileSizeKey]),
            let size = values.fileSize, size <= 2 * 1024 * 1024,
            let source = CGImageSourceCreateWithURL(file as CFURL, nil),
            let image = CGImageSourceCreateThumbnailAtIndex(source, 0, [
              kCGImageSourceCreateThumbnailFromImageAlways: true,
              kCGImageSourceCreateThumbnailWithTransform: true,
              kCGImageSourceThumbnailMaxPixelSize: 192
            ] as CFDictionary) else { return }
      let thumbnail = UIImage(cgImage: image)
      DispatchQueue.main.async {
        guard let self = self, self.representedNotification == token else { return }
        self.avatar.image = thumbnail
        self.initial.isHidden = true
      }
    }
    avatarTask?.resume()
  }

  private func relativeTime(_ data: [AnyHashable: Any], delivered: Date) -> String {
    let raw = data["created_at_iso"] ?? data["created_at"] ?? data["sent_at"]
    let value = raw.map { String(describing: $0) } ?? ""
    let date: Date
    if let epoch = Double(value) {
      date = Date(timeIntervalSince1970: epoch < 100_000_000_000 ? epoch : epoch / 1000)
    } else {
      let formatter = ISO8601DateFormatter()
      formatter.formatOptions = [.withInternetDateTime, .withFractionalSeconds]
      let fractional = formatter.date(from: value)
      formatter.formatOptions = [.withInternetDateTime]
      date = fractional ?? formatter.date(from: value) ?? delivered
    }
    let seconds = max(0, Date().timeIntervalSince(date))
    if seconds < 60 { return "Just now" }
    if seconds < 3600 { return "\(Int(seconds / 60))m ago" }
    if seconds < 86400 { return "\(Int(seconds / 3600))h ago" }
    return "\(Int(seconds / 86400))d ago"
  }

  private func avatarColor(_ name: String) -> UIColor {
    let palette: [UInt32] = [0x5F33E1, 0x0E7C86, 0xC2410C, 0xBE185D, 0x1D4ED8, 0x047857, 0xB45309, 0x7C3AED]
    let hash = name.lowercased().utf16.reduce(UInt32(0)) { (($0 &* 31) &+ UInt32($1)) & 0x7fffffff }
    let value = palette[Int(hash) % palette.count]
    return UIColor(red: CGFloat((value >> 16) & 255) / 255,
                   green: CGFloat((value >> 8) & 255) / 255,
                   blue: CGFloat(value & 255) / 255, alpha: 1)
  }

  deinit { avatarTask?.cancel() }
}
