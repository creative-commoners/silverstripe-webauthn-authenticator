/* global window */

import React, { Component } from 'react';
import { base64ToByteArray, byteArrayToBase64 } from 'lib/convert';

class Verify extends Component {
  constructor(props) {
    super(props);
    this.state = {
      failed: false,
    };
    this.handleStartAuth = this.handleStartAuth.bind(this);
  }

  initAuth() {
    const { publicKey, onCompleteVerification } = this.props;

    const parsed = {
      ...publicKey,
      challenge: base64ToByteArray(publicKey.challenge),
      allowCredentials: publicKey.allowCredentials.map(data => ({
        ...data,
        id: base64ToByteArray(data.id),
      })),
    };

    this.setState({
      failed: false,
    });

    navigator.credentials.get({ publicKey: parsed })
      .then(response => {
        onCompleteVerification({
          credentials: btoa(JSON.stringify({
            id: response.id,
            type: response.type,
            rawId: byteArrayToBase64(response.rawId),
            response: {
              clientDataJSON: byteArrayToBase64(response.response.clientDataJSON),
              authenticatorData: byteArrayToBase64(response.response.authenticatorData),
              signature: byteArrayToBase64(response.response.signature),
              userHandle: response.response.userHandle
                ? byteArrayToBase64(response.response.userHandle)
                : null,
            },
          })),
        });
      })
      .catch(error => {
        this.setState({
          failed: error,
        });
      });
  }

  handleStartAuth(event) {
    event.preventDefault();
    this.initAuth();
  }

  /**
   * Render a description for this input
   *
   * @return {HTMLElement}
   */
  renderFailureDescription() {
    const { ss: { i18n } } = window;
    const { moreOptionsControl } = this.props;

    return (
      <div>
        <p>
          {i18n._t(
            'MFAWebAuthnVerify.FAIL_DESCRIPTION',
            'Something seems to have gone wrong with your authentication.'
          )}
        </p>
        {moreOptionsControl}
        <button onClick={this.handleStartAuth}>Try again</button>
      </div>
    );
  }

  renderSpinner() {
    const { ss: { i18n } } = window;

    return (
      <p>
        {i18n._t(
          'MFAWebAuthnVerify.USE_DESCRIPTION',
          'Use your hardware security device...'
        )}
      </p>
    );
  }


  render() {
    const { failed } = this.state;

    return (
      <form className="mfa-verify-web-authn__container">
        <div className="mfa-verify-web-authn__content">
          { failed ? this.renderFailureDescription() : this.renderSpinner() }
        </div>
        <div className="mfa-verify-web-authn__icon" />
      </form>
    );
  }
}

export default Verify;
