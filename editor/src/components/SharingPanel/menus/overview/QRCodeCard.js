import React from 'react'
import { Card } from 'react-bootstrap'

import { BrandedQRCode } from 'components'

export const QRCodeCard = ({ link }) => {
  return (
    <div className="col-md-3 d-flex align-items-stretch">
      <Card className="h-100 w-100 d-flex justify-content-center align-items-center p-3">
        <BrandedQRCode value={link} />
      </Card>
    </div>
  )
}
