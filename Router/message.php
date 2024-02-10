<?php

declare(strict_types=1);

namespace Extra;

/** Message output handler. */
class Message
{
  # PROPERTIES
  // api status.
  private $status_ = 200;
  // api status text.
  private $status_text = 'OK';
  // content
  private mixed $content = null;
  // http status.
  private int $http_status = HttpStatus::OK; // 200
  // api status message
  private ?string $status_message = null;
  // content type
  private MessageType $content_type = MessageType::isAPI;

  # METHODS
  function __construct(
    mixed $content = null,
    MessageType $messageType = MessageType::isAPI,

    int|HttpStatus $httpStatus = HttpStatus::OK,
    int $status = 0,
    string $status_message = null,
    string $status_text = 'OK',
  ) {
    $this->content = $content;
    $this->content_type = $messageType;
    $this->http_status = $httpStatus;
    $this->status_ = $status;
    $this->status_message = $status_message;
    $this->status_text = $status_text;
  }

  /** set some API params. */
  public function api(
    int $level,
    ?string $message = null,
    ?string $status_text = null
  ): Message {
    $this->status_ = $level;
    $this->status_message = $message;
    $this->status_text = $status_text;
    return $this;
  }

  /** set http status. */
  public function status(int|HttpStatus $level = HttpStatus::OK): Message
  {
    $this->status_ = $level;
    return $this;
  }

  /** Print and Die.
   * 
   * if `message` is provided only it is printed.
   */
  public function pnd(mixed $message = null): void
  {
    $this->handler(message: $message);
    exit(0);
  }

  /** Print message
   * 
   * if `message` is provided only it is printed.
   */
  public function print(mixed $message = null): Message
  {
    $this->handler(message: $message);
    return $this;
  }

  private function handler(mixed $message = null): void
  {
    $content_type_ = 'application/json';
    switch ($this->content_type) {
      case MessageType::isPLAIN:
        $content_type_ = 'text/plain';
        break;
      case MessageType::isJSON:
        $content_type_ = 'application/json';
        break;
      case MessageType::isAPI:
        $content_type_ = 'application/json';
        break;
      case MessageType::isHTML:
        $content_type_ = 'text/html';
        break;
    }
    // header('HTTP/1.1 ');
    header(
      header: 'Content-Type: ' . $content_type_,
      replace: true,
      response_code: $this->http_status
    );

    // response
    // echo $content_type_;
    if ($this->content_type != MessageType::isAPI) {
      $this->content = !is_null($message) ? $message : $this->content;
      switch ($content_type_) {
        case 'application/json':
          echo json_encode($this->content);
          break;
        case 'text/plain':
          print $this->content;
          break;
        case 'text/html':
          // echo htmlspecialchars($this->content);
          print $this->content;
          break;
      }

      // * API response *
    } elseif ($this->content_type == MessageType::isAPI && is_null($message)) {
      echo json_encode([
        'http_status'     => $this->http_status,
        'status'          => $this->status_,
        'status_text'     => $this->status_text,
        'status_message'  => $this->status_message,
        "content"         => $this->content,
      ]);
    }
  }
}

/** `Message` response type. */
enum MessageType
{
  case isJSON;
  case isPLAIN;
  case isHTML;
  case isAPI;
}

class HttpStatus
{
  const NOT_FOUND = 404;
  const ERROR = 500;
  const OK = 200;
}
